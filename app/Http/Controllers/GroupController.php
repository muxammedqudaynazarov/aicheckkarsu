<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Group;
use App\Models\Specialty;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::where('structure', '11')->orderBy('name')->get();
        $specialties = collect();

        $query = Group::with(['specialty.department', 'students', 'lessons']);

        // Fakultet bo'yicha filtr
        if ($request->filled('department_id')) {
            $specialties = Specialty::where('department_id', $request->department_id)
                ->orderBy('name')
                ->get();

            $query->whereHas('specialty', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // Mutaxassislik bo'yicha filtr
        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }

        // NOMI BO'YICHA QIDIRUV
        if ($request->filled('search_name')) {
            $query->where('name', 'like', '%' . $request->search_name . '%');
        }

        $groups = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('pages.hemis.groups', compact('groups', 'departments', 'specialties'));
    }
}
