<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(10);
        $role = Role::all();
        $roles = [];
        foreach ($role as $r) {
            $roles[$r->name] = $r->desc;
        }
        return view('pages.users.index', compact(['users', 'roles']));
    }

    public function edit(User $user)
    {
        $departments = Department::whereNull('parent_id')->get()->groupBy('structure');
        return view('pages.users.edit', compact(['user', 'departments']));
    }
}
