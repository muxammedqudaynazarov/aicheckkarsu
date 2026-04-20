<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Lesson;
use App\Models\Option;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $options = Option::pluck('value', 'key')->toArray();
        if (auth()->user()->can('dashboard.view')) {
            $lessons = Lesson::latest()->take(10)->get();
            $counters = [
                'lessons' => Lesson::count(),
                'files' => File::count(),
                'students' => Student::count(),
                'today' => File::whereDate('created_at', Carbon::today())->count(),
            ];
            return view('home', compact('lessons', 'counters', 'options'));
        }
        return view('wait', compact('options'));
    }

    public function role($role)
    {
        $user = auth()->user();
        $user->pos = $role;
        $user->syncRoles($role); // Eski rollarni olib tashlab yangisini qo'shadi
        $user->syncPermissions([]); // Ortiqcha shaxsiy ruxsatlarni tozalaydi
        $user->save();

        return redirect()->route('home');
    }
}
