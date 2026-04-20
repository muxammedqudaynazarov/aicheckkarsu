<?php

namespace App\Http\Controllers;

use App\Models\Curriculum;
use App\Models\Department;
use Illuminate\Http\Request;

class CurriculumController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::where('structure', '11')->orderBy('name')->get();

        $query = Curriculum::with(['department', 'groups']);

        // Fakultet bo'yicha filtr
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // NOMI BO'YICHA QIDIRUV
        if ($request->filled('search_name')) {
            $query->where('name', 'like', '%' . $request->search_name . '%');
        }

        $curricula = $query->orderBy('name')->paginate(auth()->user()->per_page)->withQueryString();

        return view('pages.hemis.curricula', compact('curricula', 'departments'));
    }
}
