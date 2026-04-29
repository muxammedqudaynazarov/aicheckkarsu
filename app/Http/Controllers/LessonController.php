<?php

namespace App\Http\Controllers;

use App\Jobs\CheckLessonFiles;
use App\Models\Account;
use App\Models\Department;
use App\Models\EduYear;
use App\Models\File;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Semester;
use App\Models\Specialty;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user->can('lessons.view')) {
            return redirect()->back()->with('error', 'Sizda bu sahifaga kirish huquqi yo‘q.');
        }
        Account::whereDate('reloaded_at', '!=', Carbon::today())->orWhereNull('reloaded_at')
            ->update(['rpd' => DB::raw('rpd_default'), 'reloaded_at' => Carbon::now()]);
        $availableRpd = Account::where('status', '0')
            ->when($user->pos === 'moder', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->sum('rpd');
        $lessons = Lesson::with(['group'])
            ->when(!in_array($user->pos, ['moder', 'admin', 'super_admin']), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->withCount(['files as pending_files_count' => function ($query) {
                $query->where('participant', '0')->where('status', '0');
            }])->latest('id')->paginate($user->per_page);
        return view('pages.lessons.index', compact(['lessons', 'availableRpd']));
    }

    public function startChecking(Lesson $lesson)
    {
        $user = auth()->user();
        if ($lesson->status === '0') {
            $pendingFiles = $lesson->files()->where('participant', '0')->where('status', '0')->count();
            if ($pendingFiles === 0) {
                return back()->with('error', 'Bu imtihonda tekshirilishi kerak bo‘lgan fayllar mavjud emas.');
            }
            $availableRpd = Account::where('status', '0')
                ->when($user->pos === 'moder', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->sum('rpd');
            if ($availableRpd < $pendingFiles) {
                return back()->with('error', "API akkauntlarda yetarli limit mavjud emas! Talab etiladi: {$pendingFiles} ta, Mavjud limit: {$availableRpd} ta.");
            }
            $lesson->update(['status' => '1']);
            CheckLessonFiles::dispatch($lesson, $user);
            return back()->with('success', 'Imtihon qog‘ozlari tekshirish uchun navbatga qo‘yildi (AI).');
        }
        return back()->with('error', 'Bu imtihon allaqachon tekshirilmoqda yoki yakunlangan.');
    }

    public function create()
    {
        if (!auth()->user()->can('lessons.create')) return redirect()->back()->with('error', 'Sizda huquq yo‘q.');
        $departments = Department::where('structure', '11')->orderBy('name')->get();
        return view('pages.lessons.create', compact('departments'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('lessons.create')) return redirect()->back()->with('error', 'Ruxsat yo‘q.');
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'name' => 'required|string|max:255',
            'files.*' => 'nullable|file|mimes:pdf|max:10240',
        ]);
        $examDate = Carbon::createFromFormat('d.m.Y', $request->exam_date)->format('Y-m-d 12:00:00');
        $meta = session('student_meta');
        $lesson = Lesson::create([
            'name' => $request->name,
            'uuid' => uniqid(),
            'group_id' => $request->group_id,
            'user_id' => auth()->id(),
            'level_id' => $meta['level_id'],
            'semester_id' => $meta['semester_id'],
            'edu_year_id' => $meta['edu_year_id'],
            'exam_date' => $examDate,
        ]);
        $allStudentIds = Student::where('group_id', $lesson->group_id)->pluck('id');
        $uploadedFiles = $request->file('files', []);
        $filesData = []; // Barcha fayllarni bittada saqlash uchun massiv
        foreach ($allStudentIds as $studentId) {
            if (isset($uploadedFiles[$studentId])) {
                $file = $uploadedFiles[$studentId];
                $year_id = date('Y');
                $faculty_id = $lesson->group->specialty->department_id ?? 'default_faculty';
                $specialty_id = $lesson->group->specialty_id ?? 'default_specialty';
                $group_id = $lesson->group_id ?? 'default_group';
                $exam_date_format = date('dmY', strtotime($lesson->exam_date));
                $fileName = "student_{$studentId}_" . time() . "." . $file->getClientOriginalExtension();
                $dir = "exams/{$year_id}/{$faculty_id}/{$specialty_id}/{$group_id}/lesson_{$lesson->id}_{$exam_date_format}";
                $path = $file->storeAs($dir, $fileName, 'public');
                $filesData[] = [
                    'file_url' => $path,
                    'uuid' => uniqid(),
                    'student_id' => $studentId,
                    'lesson_id' => $lesson->id,
                    'participant' => '0',
                    'status' => '0',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                $filesData[] = [
                    'file_url' => null,
                    'uuid' => uniqid(),
                    'student_id' => $studentId,
                    'lesson_id' => $lesson->id,
                    'participant' => '1',
                    'status' => '2',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        File::insert($filesData); // Barcha ma'lumotlarni 1 ta so'rov bilan yozish (O'ta tez)
        return redirect()->route('lessons.index')->with('success', 'Fayllar yuklandi!');
    }

    public function getSpecialties($departmentId)
    {
        return response()->json(Specialty::where('department_id', $departmentId)->orderBy('name')->get());
    }

    public function getGroups($specialtyId)
    {
        return response()->json(Group::where('specialty_id', $specialtyId)->orderBy('name')->get());
    }

    public function syncStudents(Request $request)
    {
        $groupId = $request->group_id;
        try {
            $response = Http::timeout(30)->withToken(env('HEMIS_API'))->get('https://student.karsu.uz/rest/v1/data/student-list', [
                '_group' => $groupId,
                'limit' => 200
            ]);
            if ($response->successful()) {
                $studentsData = $response->json();
                if (!empty($studentsData['data']['items'])) {
                    $firstStudent = $studentsData['data']['items'][0];
                    $level = Level::firstOrCreate(['id' => $firstStudent['level']['code']], ['name' => $firstStudent['level']['name']]);
                    $semester = Semester::firstOrCreate(['id' => $firstStudent['semester']['code']], ['name' => $firstStudent['semester']['name']]);
                    $edu_year = EduYear::firstOrCreate(['id' => $firstStudent['educationYear']['code']], ['name' => $firstStudent['educationYear']['name']]);
                    session(['student_meta' => [
                        'level_id' => $level->id,
                        'semester_id' => $semester->id,
                        'edu_year_id' => $edu_year->id,
                    ]]);
                    $studentsToInsert = [];
                    foreach ($studentsData['data']['items'] as $student) {
                        $studentsToInsert[] = [
                            'id' => $student['id'],
                            'name' => $student['full_name'],
                            'student_id_number' => $student['student_id_number'],
                            'group_id' => $groupId,
                        ];
                    }
                    // Bitta massivga yig'ib faqat 1 marta bazaga murojaat qilamiz (Upsert - bor bo'lsa yangilaydi, yo'q bo'lsa yaratadi)
                    Student::upsert($studentsToInsert, ['id'], ['name', 'student_id_number', 'group_id']);
                }
            }
        } catch (\Exception $e) {
            Log::error("HEMIS API xatosi: " . $e->getMessage());
        }
        return response()->json(Student::where('group_id', $groupId)->orderBy('name')->get());
    }

    public function edit(Lesson $lesson)
    {
        $user = auth()->user();
        if (!$user->can('lessons.create')) {
            return redirect()->back()->with('error', 'Sizda imtihonlarni o‘zgartirish huquqi yo‘q.');
        }

        if ($lesson->status == '1') {
            return redirect()->route('lessons.index')
                ->with('success', 'Tekshirilmoqda holatida fanni ko‘rib bo‘lmaydi.');
        }
        $departments = Department::where('structure', '11')->orderBy('name')->get();
        $specialties = Specialty::where('department_id', $lesson->group->specialty->department->id)->orderBy('name')->get();
        $groups = Group::where('specialty_id', $lesson->group->specialty->id)->orderBy('name')->get();
        $students = Student::where('group_id', $lesson->group_id)->orderBy('name')->get();
        $files = File::where('lesson_id', $lesson->id)->get()->keyBy('student_id');
        return view('pages.lessons.edit', compact(['lesson', 'students', 'files', 'departments', 'specialties', 'groups']));
    }

    public function update(Request $request, Lesson $lesson)
    {
        $user = auth()->user();
        if (!$user->can('lessons.create')) {
            return redirect()->back()->with('error', 'Sizda imtihonlarni o‘zgartirish huquqi yo‘q.');
        }

        if ($lesson->status == '0') {
            $request->validate([
                'files.*' => 'nullable|file|mimes:pdf|max:10240',
            ]);
            $participants = $request->input('participant', []);
            $removedFiles = $request->input('remove_files', []);
            $uploadedFiles = $request->file('files', []);
            $students = Student::where('group_id', $lesson->group_id)->pluck('id');
            foreach ($students as $studentId) {
                $existingFile = File::where('lesson_id', $lesson->id)->where('student_id', $studentId)->first();
                if (isset($participants[$studentId])) {
                    if ($existingFile && $existingFile->file_url) {
                        Storage::disk('public')->delete($existingFile->file_url);
                    }
                    File::updateOrCreate(
                        ['student_id' => $studentId, 'lesson_id' => $lesson->id],
                        ['participant' => '1', 'status' => '2', 'file_url' => null]
                    );
                    continue;
                }
                if (isset($removedFiles[$studentId]) && $removedFiles[$studentId] == '1') {
                    if ($existingFile && $existingFile->file_url) {
                        Storage::disk('public')->delete($existingFile->file_url);
                        $existingFile->update(['file_url' => null]);
                    }
                }
                if (isset($uploadedFiles[$studentId])) {
                    $file = $uploadedFiles[$studentId];
                    $path = $file->store("exams/lesson_{$lesson->id}/student_{$studentId}", 'public');
                    if ($existingFile && $existingFile->file_url && $existingFile->file_url !== $path) {
                        Storage::disk('public')->delete($existingFile->file_url);
                    }
                    File::updateOrCreate(
                        ['student_id' => $studentId, 'lesson_id' => $lesson->id],
                        ['file_url' => $path, 'participant' => '0', 'status' => '0']
                    );
                } else {
                    $currentFile = File::where('lesson_id', $lesson->id)->where('student_id', $studentId)->first();
                    if (!$currentFile || !$currentFile->file_url) {
                        File::updateOrCreate(
                            ['student_id' => $studentId, 'lesson_id' => $lesson->id],
                            ['participant' => '1', 'status' => '2', 'file_url' => null]
                        );
                    }
                }
            }
            return redirect()->route('lessons.index')->with('success', 'Imtihon ma’lumotlari yangilandi!');
        }
    }

    public function destroy(Lesson $lesson)
    {
        if (!auth()->user()->can('lessons.create')) return redirect()->back()->with('error', 'Sizda huquq yo‘q.');
        if ($lesson->status == '0') {
            // Fayllarni jismoniy o'chirish (Bittada massiv orqali o'chirish!)
            $fileUrls = $lesson->files()->whereNotNull('file_url')->pluck('file_url')->toArray();
            if (!empty($fileUrls)) {
                Storage::disk('public')->delete($fileUrls);
            }
            $lesson->files()->delete(); // DB dan bittada o'chirish
            $lesson->delete(); // Darsni o'chirish
            return redirect()->route('lessons.index')->with('success', 'Muvaffaqiyatli o‘chirildi.');
        }
        return redirect()->route('lessons.index')->with('error', 'Imtihonni o‘chirib bo‘lmaydi.');
    }
}
