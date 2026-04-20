<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\EduYear;
use App\Models\File;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Specialty;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function years()
    {
        $user = auth()->user();
        if (!$user->can('archives.view')) {
            return redirect()->back()->with('error', 'Sizda bu sahifaga kirish huquqi yo‘q.');
        }

        $years = EduYear::whereHas('lessons', function ($q) {
            $q->where('status', '2');
        })->get();
        $folders = $years->map(function ($year) {
            return [
                'name' => $year->name,
                'url' => route('drive.departments', $year->id)
            ];
        });
        return view('pages.results.drive', [
            'folders' => $folders,
            'files' => [],
            'breadcrumbs' => []
        ]);
    }

    public function departments(EduYear $year)
    {
        $user = auth()->user();
        if (!$user->can('archives.view')) {
            return redirect()->back()->with('error', 'Sizda bu sahifaga kirish huquqi yo‘q.');
        }
        $departments = Department::whereHas('specialties.groups.lessons', function ($q) use ($year) {
            $q->where('status', '2')->where('edu_year_id', $year->id);
        })->get();
        $folders = $departments->map(function ($dept) use ($year) {
            return [
                'name' => $dept->name,
                'url' => route('drive.specialties', ['year' => $year->id, 'department' => $dept->id])
            ];
        });
        return view('pages.results.drive', [
            'folders' => $folders,
            'files' => [],
            'breadcrumbs' => [
                ['name' => $year->name, 'url' => '#']
            ]
        ]);
    }

    public function specialties(EduYear $year, Department $department)
    {
        $user = auth()->user();
        if (!$user->can('archives.view')) {
            return redirect()->back()->with('error', 'Sizda bu sahifaga kirish huquqi yo‘q.');
        }
        $specialties = Specialty::where('department_id', $department->id)
            ->whereHas('groups.lessons', function ($q) use ($year) {
                $q->where('status', '2')->where('edu_year_id', $year->id);
            })->get();
        $folders = $specialties->map(function ($spec) use ($year, $department) {
            return [
                'name' => $spec->code . ' – ' . $spec->name,
                'url' => route('drive.levels', ['year' => $year->id, 'department' => $department->id, 'specialty' => $spec->id])
            ];
        });
        return view('pages.results.drive', [
            'folders' => $folders,
            'files' => [],
            'breadcrumbs' => [
                ['name' => $year->name, 'url' => route('drive.departments', $year->id)],
                ['name' => $department->name, 'url' => '#']
            ]
        ]);
    }

    public function levels(EduYear $year, Department $department, Specialty $specialty)
    {
        $user = auth()->user();
        if (!$user->can('archives.view')) {
            return redirect()->back()->with('error', 'Sizda bu sahifaga kirish huquqi yo‘q.');
        }
        $levels = Level::whereHas('lessons', function ($q) use ($year, $specialty) {
            $q->where('status', '2')->where('edu_year_id', $year->id)
                ->whereHas('group', function ($g) use ($specialty) {
                    $g->where('specialty_id', $specialty->id);
                });
        })->get();
        $folders = $levels->map(function ($level) use ($year, $department, $specialty) {
            return [
                'name' => $level->name,
                'url' => route('drive.lessons', ['year' => $year->id, 'department' => $department->id, 'specialty' => $specialty->id, 'level' => $level->id])
            ];
        });
        return view('pages.results.drive', [
            'folders' => $folders,
            'files' => [],
            'breadcrumbs' => [
                ['name' => $year->name, 'url' => route('drive.departments', $year->id)],
                ['name' => $department->name, 'url' => route('drive.specialties', ['year' => $year->id, 'department' => $department->id])],
                ['name' => $specialty->code . ' – ' . $specialty->name, 'url' => '#']
            ]
        ]);
    }

    public function lessons(EduYear $year, Department $department, Specialty $specialty, Level $level)
    {
        $user = auth()->user();
        if (!$user->can('archives.view')) {
            return redirect()->back()->with('error', 'Sizda bu sahifaga kirish huquqi yo‘q.');
        }
        $files = Lesson::with(['group', 'files'])->where('status', '2')
            ->where('edu_year_id', $year->id)->where('level_id', $level->id)
            ->whereHas('group', function ($g) use ($specialty) {
                $g->where('specialty_id', $specialty->id);
            })->get();
        $url = [
            'year' => $year->id,
            'department' => $department->id,
            'specialty' => $specialty->id,
            'level' => $level->id,
        ];
        return view('pages.results.drive', [
            'folders' => [],
            'files' => $files,
            'url' => $url,
            'breadcrumbs' => [
                ['name' => $year->name, 'url' => route('drive.departments', $year->id)],
                ['name' => $department->name, 'url' => route('drive.specialties', ['year' => $year->id, 'department' => $department->id])],
                ['name' => $specialty->code . ' – ' . $specialty->name, 'url' => route('drive.levels', ['year' => $year->id, 'department' => $department->id, 'specialty' => $specialty->id])],
                ['name' => $level->name, 'url' => '#']
            ]
        ]);
    }

    public function lesson(EduYear $year, Department $department, Specialty $specialty, Level $level, Lesson $lesson)
    {
        $user = auth()->user();
        if (!$user->can('archives.view')) {
            return redirect()->back()->with('error', 'Sizda bu sahifaga kirish huquqi yo‘q.');
        }
        if ($lesson->status == '1') {
            return redirect()->route('lessons.index')->with('success', 'Tekshirilmoqda holatida fanni ko‘rib bo‘lmaydi.');
        }
        $group = $lesson->group;
        $students = Student::where('group_id', $lesson->group_id)->orderBy('name')->get();
        $files = File::where('lesson_id', $lesson->id)->get()->keyBy('student_id');
        $breadcrumbs = [
            ['name' => $year->name, 'url' => route('drive.departments', $year->id)],
            ['name' => $department->name, 'url' => route('drive.specialties', ['year' => $year->id, 'department' => $department->id])],
            ['name' => $specialty->code . ' – ' . $specialty->name, 'url' => route('drive.levels', ['year' => $year->id, 'department' => $department->id, 'specialty' => $specialty->id])],
            ['name' => $level->name, 'url' => route('drive.lessons', ['year' => $year->id, 'department' => $department->id, 'specialty' => $specialty->id, 'level' => $level->id])],
            ['name' => $group->name . '</li><li class="breadcrumb-item active">' . $lesson->name, 'url' => '#']
        ];
        return view('pages.results.lesson', compact([
            'lesson',
            'students',
            'files',
            'department',
            'specialty',
            'group',
            'level',
            'year',
            'breadcrumbs'
        ]));
    }

    public function uploadScanned(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('archives.view')) {
            return redirect()->back()->with('error', 'Sizda bu sahifaga kirish huquqi yo‘q.');
        }
        $base64File = $request->input('file_data');
        if (preg_match('/^data:(application\/pdf|image\/\w+);base64,/', $base64File, $type)) {
            $data = substr($base64File, strpos($base64File, ',') + 1);
            $extension = 'pdf';
            if (strpos($type[1], 'image/') !== false) {
                $extension = str_replace('image/', '', strtolower($type[1]));
            }
            $data = base64_decode($data);
            if ($data === false) {
                return response()->json(['success' => false, 'message' => 'Base64 decodlashda xatolik.']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Noto‘g‘ri ma‘lumot formati. Faqat PDF yoki rasm.']);
        }
        $fileName = 'scanned_' . time() . '.' . $extension;
        $path = 'public/scans/' . $fileName;
        Storage::put($path, $data);
        return response()->json([
            'success' => true,
            'message' => 'Fayl muvaffaqiyatli saqlandi!',
            'file_url' => Storage::url($path)
        ]);
    }
}
