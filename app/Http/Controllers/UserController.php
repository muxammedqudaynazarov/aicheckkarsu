<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user->can('users.view')) return redirect()->back()->with('error', 'Sizda bu sahifaga kirish huquqi yo‘q.');
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

    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        if (!$currentUser->can('users.edit')) {
            return redirect()->route('users.index')->with('error', 'Sizda foydalanuvchilarni tahrirlash huquqi yo‘q.');
        }
        $request->validate([
            'department' => 'nullable|exists:departments,id',
            'pos' => 'nullable|in:user,uploader,moder,admin',
        ], [
            'department.exists' => 'Tanlangan bo‘lim tizimda mavjud emas.',
            'pos.in' => 'Noto‘g‘ri rol tanlandi.',
        ]);
        $newPos = $user->pos;
        if ($user->pos !== 'super_admin' && $request->filled('pos')) {
            $newPos = $request->pos;
        }
        $user->update([
            'department_id' => $request->department,
            'pos' => $newPos,
        ]);
        if ($user->pos !== 'super_admin' && $request->filled('pos')) {
            $user->syncRoles($newPos);
            $user->syncPermissions([]);
        }
        return redirect()->route('users.index')->with('success', 'Foydalanuvchi ma’lumotlari muvaffaqiyatli yangilandi!');
    }
}
