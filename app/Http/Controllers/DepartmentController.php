<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::where('structure', '11')
            ->with([
                'specialties',
                'groups.students',
                'groups.lessons'
            ])
            ->orderBy('name')
            ->paginate(auth()->user()->per_page);

        return view('pages.hemis.departments', compact(['departments']));
    }
}
