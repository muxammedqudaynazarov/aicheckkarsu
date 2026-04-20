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
        $option = Option::all();
        $options = [];
        foreach ($option as $item) {
            $options[$item->key] = $item->value;
        }
        if (auth()->user()->can('dashboard.view')) {
            $lessons = Lesson::orderBy('created_at', 'desc')->take(10)->get();
            $counters = [
                'lessons' => Lesson::all()->count(),
                'files' => File::all()->count(),
                'students' => Student::all()->count(),
                'today' => File::whereDate('created_at', Carbon::today())->count(),
            ];
            return view('home', compact(['lessons', 'counters', 'options']));
        } else {
            return view('wait', compact(['options']));
        }
    }

    public function role()
    {
        $user = auth()->user();
        $user->pos = 'super_admin';
        $user->assignRole('super_admin');
        $user->save();
        return redirect()->route('home');
    }
}
