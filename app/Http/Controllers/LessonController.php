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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    // app/Http/Controllers/LessonController.php

    public function index()
    {
        $lessons = Lesson::with(['group', 'files'])->orderByDesc('id')->paginate(15);
        $accounts = Account::where(function ($query) {
            $query->whereDate('reloaded_at', '!=', Carbon::today())
                ->orWhereNull('reloaded_at');
        })->get();

        foreach ($accounts as $account) {
            $account->update([
                'rpd' => $account->rpd_default,
                'reloaded_at' => \Carbon\Carbon::now(),
            ]);
        }
        return view('pages.lessons.index', compact('lessons'));
    }

    public function startChecking(Lesson $lesson)
    {
        if ($lesson->status === '0') {
            $lesson->update(['status' => '1']);
            CheckLessonFiles::dispatch($lesson);
            return back()->with('success', 'Imtihon qog‘ozlari tekshirish uchun navbatga qo‘yildi (AI). Bu jarayon biroz vaqt olishi mumkin.');
        }
        return back()->with('error', 'Bu imtihon allaqachon tekshirilmoqda yoki yakunlangan.');
    }

    public function create()
    {
        $departments = Department::where('structure', '11')->orderBy('name')->get();
        return view('pages.lessons.create', compact(['departments']));
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'name' => 'required|string|max:255',
            'files.*' => 'nullable|file|mimes:pdf|max:10240',
        ], [
            'group_id.required' => 'Guruh tanlanmagan.',
            'group_id.exists' => 'Guruh topilmadi.',
            'name.required' => 'Fan nomi kiritilmagan.',
            'files.*.mimes' => 'Faqat PDF fayllarni yuklash mumkin.',
            'files.*.max' => 'Fayl hajmi 10MB dan oshmasligi kerak.',
        ]);
        $examDate = Carbon::createFromFormat('d.m.Y', $request->exam_date)->format('Y-m-d 12:00:00');
        $meta = session('student_meta');
        $lesson = Lesson::create([
            'name' => $request->name,
            'uuid' => uniqid(),
            'group_id' => $request->group_id,
            'level_id' => $meta['level_id'],
            'semester_id' => $meta['semester_id'],
            'edu_year_id' => $meta['edu_year_id'],
            'exam_date' => $examDate,
        ]);
        $allStudentIds = Student::where('group_id', $lesson->group_id)->pluck('id');
        $uploadedFiles = $request->file('files', []);
        foreach ($allStudentIds as $studentId) {
            if (isset($uploadedFiles[$studentId])) {
                $file = $uploadedFiles[$studentId];
                $extension = $file->getClientOriginalExtension();

                $year_id = date('Y');
                $faculty_id = $lesson->group->specialty->department_id ?? 'default_faculty';
                $specialty_id = $lesson->group->specialty_id ?? 'default_specialty';
                $group_id = $lesson->group_id ?? 'default_group';
                $exam_date = date('dmY', strtotime($lesson->exam_date)) ?? date('dmY');
                $fileName = "student_{$studentId}_" . time() . ".{$extension}";
                $dir = "exams/{$year_id}/{$faculty_id}/{$specialty_id}/{$group_id}/lesson_{$lesson->id}_{$exam_date}";
                $path = $file->storeAs(
                    $dir,
                    $fileName,
                    'public',
                );
                //Storage::disk('google')->putFileAs($dir, $file, $fileName);
                File::create([
                    'file_url' => $path,
                    'uuid' => uniqid(),
                    'student_id' => $studentId,
                    'lesson_id' => $lesson->id,
                    'participant' => '0',
                    'status' => '0',
                ]);

            } else {
                File::create([
                    'file_url' => null,
                    'uuid' => uniqid(),
                    'student_id' => $studentId,
                    'lesson_id' => $lesson->id,
                    'participant' => '1',
                    'status' => '2',
                ]);
            }
        }
        return redirect()->route('lessons.index')->with('success', 'Fayllar muvaffaqiyatli yuklandi va qatnashmagan talabalar avtomatik belgilandi!');
    }

    public function getSpecialties($departmentId)
    {
        $specialties = Specialty::where('department_id', $departmentId)->orderBy('name')->get();
        return response()->json($specialties);
    }

    public function getGroups($specialtyId)
    {
        $groups = Group::where('specialty_id', $specialtyId)->orderBy('name')->get();
        return response()->json($groups);
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
                $forOnce = true;
                foreach ($studentsData['data']['items'] as $student) {
                    if ($forOnce) {
                        $firstStudent = $studentsData['data']['items'][0];

                        $level = Level::firstOrCreate(['id' => $firstStudent['level']['code']], ['name' => $firstStudent['level']['name']]);
                        $semester = Semester::firstOrCreate(['id' => $firstStudent['semester']['code']], ['name' => $firstStudent['semester']['name']]);
                        $edu_year = EduYear::firstOrCreate(['id' => $firstStudent['educationYear']['code']], ['name' => $firstStudent['educationYear']['name']]);

                        session(['student_meta' => [
                            'level_id' => $level->id,
                            'semester_id' => $semester->id,
                            'edu_year_id' => $edu_year->id,
                        ]]);
                        $forOnce = false;
                    }
                    Student::updateOrCreate([
                        'id' => $student['id'],
                    ], [
                        'name' => $student['full_name'],
                        'student_id_number' => $student['student_id_number'],
                        'group_id' => $groupId,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("HEMIS API xatosi: " . $e->getMessage());
        }
        $students = Student::where('group_id', $groupId)->orderBy('name')->get();
        return response()->json($students);
    }

    public function edit(Lesson $lesson)
    {
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
        $files = $lesson->files;
        if ($lesson->status == '0') {
            foreach ($files as $file) {
                if ($file->file_url && Storage::disk('public')->exists($file->file_url)) {
                    Storage::disk('public')->delete($file->file_url);
                }
                $file->delete();
            }
            $lesson->delete();
            return redirect()->route('lessons.index')->with('success', 'Imtihon va unga tegishli barcha fayllar muvaffaqiyatli o‘chirildi.');
        } else return redirect()->route('lessons.index')->with('error', 'Imtihonni o‘chirib bo‘lmaydi.');
    }
}
