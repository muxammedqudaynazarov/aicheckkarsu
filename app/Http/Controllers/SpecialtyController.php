<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Specialty;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::where('structure', '11')->orderBy('name')->get();

        $query = Specialty::with(['department', 'groups.students', 'groups.lessons']);

        // Fakultet bo'yicha filtr
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // NOMI BO'YICHA QIDIRUV
        if ($request->filled('search_name')) {
            $query->where('name', 'like', '%' . $request->search_name . '%');
        }

        $specialties = $query->orderBy('name')->paginate(auth()->user()->per_page)->withQueryString();

        return view('pages.hemis.specialties', compact('specialties', 'departments'));
    }
}
