<?php

namespace App\Http\Controllers;

use App\Models\{Department, Lesson, Result, Specialty, Student, File};
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PDF;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('reports.view')) return redirect()->back()->with('error', 'Ruxsat yo‘q.');
        $departments = Department::where('structure', '11')->orderBy('name')->get();
        $specialties = $request->filled('department_id')
            ? Specialty::where('department_id', $request->department_id)->orderBy('name')->get()
            : collect();
        // when() orqali dinamik va toza Query yozish
        $lessons = Lesson::with(['group', 'files'])->where('status', '2')
            ->when($request->search_name, fn($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->department_id, fn($q, $dept) => $q->whereHas('group.specialty', fn($sq) => $sq->where('department_id', $dept)))
            ->when($request->specialty_id, fn($q, $spec) => $q->whereHas('group', fn($gq) => $gq->where('specialty_id', $spec)))
            ->orderByDesc('id')
            ->paginate($user->per_page)
            ->withQueryString();
        return view('pages.results.index', compact('lessons', 'departments', 'specialties'));
    }

    public function downloadCertificate(Lesson $lesson, Student $student)
    {
        $file = File::where('lesson_id', $lesson->id)->where('student_id', $student->id)->with('results')->firstOrFail();
        $qrCodeBase64 = base64_encode(QrCode::format('svg')->size(120)->generate(route('verify.certificate', $file->uuid)));
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
        return PDF::loadView('pages.pdf.certificate', $data)->stream('Taqriz_' . $student->student_id_number . '_' . time() . '.pdf');
    }

    public function verifyCertificate($uuid)
    {
        $file = File::with(['student', 'lesson.group.specialty.department', 'results'])->where('uuid', $uuid)->firstOrFail();
        return view('pages.verify.certificate', [
            'uniqueId' => $uuid,
            'file' => $file,
            'student' => $file->student,
            'lesson' => $file->lesson,
            'group' => $file->lesson->group,
            'specialty' => $file->lesson->group->specialty,
            'department' => $file->lesson->group->specialty->department,
            'language' => $file->lesson->group->language ?? (object)['name' => 'O\'zbek'],
            'results' => $file->results
        ]);
    }

    public function downloadStatement(Lesson $lesson)
    {
        $students = Student::where('group_id', $lesson->group_id)->orderBy('name')->get();
        $qrCodeBase64 = base64_encode(QrCode::format('svg')->size(120)->generate(route('verify.statement', $lesson->uuid)));
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
        return PDF::loadView('pages.pdf.statement', $data)->download('Bayonat_' . $lesson->uuid . '_' . time() . '.pdf');
    }

    public function verifyStatement($uuid)
    {
        $lesson = Lesson::with(['group.specialty.department'])->where('uuid', $uuid)->firstOrFail();
        $students = Student::where('group_id', $lesson->group_id)
            ->with(['files' => fn($query) => $query->where('lesson_id', $lesson->id)->with('results')])
            ->orderBy('name')->get();
        return view('pages.verify.statement', [
            'uniqueId' => $uuid,
            'lesson' => $lesson,
            'group' => $lesson->group,
            'specialty' => $lesson->group->specialty,
            'department' => $lesson->group->specialty->department,
            'students' => $students
        ]);
    }
}
