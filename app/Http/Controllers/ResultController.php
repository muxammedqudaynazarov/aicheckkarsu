<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\File;
use App\Models\Lesson;
use App\Models\Result;
use App\Models\Specialty;
use App\Models\Student;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PDF;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::where('structure', '11')->orderBy('name')->get();
        $specialties = collect();
        if ($request->filled('department_id')) {
            $specialties = Specialty::where('department_id', $request->department_id)->orderBy('name')->get();
        }
        $query = Lesson::with(['group', 'files'])->where('status', '2');
        if ($request->filled('search_name')) {
            $query->where('name', 'like', '%' . $request->search_name . '%');
        }
        if ($request->filled('department_id')) {
            $query->whereHas('group.specialty', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
        if ($request->filled('specialty_id')) {
            $query->whereHas('group', function ($q) use ($request) {
                $q->where('specialty_id', $request->specialty_id);
            });
        }
        $lessons = $query->orderByDesc('id')->paginate(auth()->user()->per_page)->withQueryString();
        return view('pages.results.index', compact(['lessons', 'departments', 'specialties']));
    }

    public function downloadCertificate(Lesson $lesson, Student $student)
    {
        $file = File::where('lesson_id', $lesson->id)->where('student_id', $student->id)->with('results')->firstOrFail();
        $verifyUrl = route('verify.certificate', $file->uuid);
        $qrCodeBase64 = base64_encode(QrCode::format('svg')->size(120)->generate($verifyUrl));
        $data = [
            'lesson' => $lesson,
            'student' => $student,
            'group' => $lesson->group,
            'specialty' => $lesson->group->specialty,
            'department' => $lesson->group->specialty->department,
            'language' => $lesson->group->language,
            'file' => $file,
            'results' => $file->results,
            'qrCode' => $qrCodeBase64,
            'uniqueId' => $file->uuid,
        ];
        //dd($lesson->semester);
        $pdf = PDF::loadView('pages.pdf.certificate', $data);
        $fileName = 'Taqriz_' . $student->student_id_number . '_' . time() . '.pdf';
        return $pdf->stream($fileName);
    }

    public function verifyCertificate($uuid)
    {
        $file = File::where('uuid', $uuid)->firstOrFail();
        $student = $file->student;
        $lesson = $file->lesson;
        $group = $lesson->group;
        $specialty = $group->specialty;
        $department = $specialty->department;
        $language = $group->language ?? (object)['name' => 'O\'zbek'];
        $results = Result::where('file_id', $file->id)->get();

        return view('pages.verify.certificate', [
            'uniqueId' => $uuid,
            'file' => $file,
            'student' => $student,
            'lesson' => $lesson,
            'group' => $group,
            'specialty' => $specialty,
            'department' => $department,
            'language' => $language,
            'results' => $results
        ]);
    }

    public function downloadStatement(Lesson $lesson)
    {
        $verifyUrl = route('verify.statement', $lesson->uuid);
        $students = Student::where('group_id', $lesson->group_id)->orderBy('name')->get();
        $qrCodeBase64 = base64_encode(QrCode::format('svg')->size(120)->generate($verifyUrl));
        $data = [
            'lesson' => $lesson,
            'group' => $lesson->group,
            'specialty' => $lesson->group->specialty,
            'department' => $lesson->group->specialty->department,
            'language' => $lesson->group->language,
            'files' => $students,
            'qrCode' => $qrCodeBase64,
            'uniqueId' => $lesson->uuid,
        ];

        $pdf = PDF::loadView('pages.pdf.statement', $data);
        $fileName = 'Bayonat_' . $lesson->uuid . '_' . time() . '.pdf';
        return $pdf->download($fileName);
    }

    public function verifyStatement($uuid)
    {
        // Lesson ni uuid orqali topamiz
        $lesson = Lesson::where('uuid', $uuid)->firstOrFail();
        $group = $lesson->group;
        $specialty = $group->specialty;
        $department = $specialty->department;

        // Guruhdagi talabalar va ularning ushbu fandagi natijalarini yuklaymiz
        $students = \App\Models\Student::where('group_id', $lesson->group_id)
            ->with(['files' => function($query) use ($lesson) {
                $query->where('lesson_id', $lesson->id)->with('results');
            }])
            ->orderBy('name')
            ->get();

        return view('pages.verify.statement', [
            'uniqueId' => $uuid,
            'lesson' => $lesson,
            'group' => $group,
            'specialty' => $specialty,
            'department' => $department,
            'students' => $students
        ]);
    }
}
