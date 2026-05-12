<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = $request->get('q');
            if ($query) {
                $subjects = Subject::where('name', 'like', '%' . $query . '%')
                    ->orderBy('name', 'asc')->limit(10)->get(['id', 'name']);
            } else {
                $subjects = [];
            }
            return response()->json($subjects);
        }
        $subjects = Subject::paginate(20);
        //return view('pages.subjects.index', compact('subjects'));
    }
}
