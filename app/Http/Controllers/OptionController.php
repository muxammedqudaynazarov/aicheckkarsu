<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OptionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('settings', compact(['user']));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'per_page' => 'required|integer|min:10|max:50',
        ], [
            'avatar.image' => 'Yuklangan fayl rasm formatida bo‘lishi kerak.',
            'avatar.max' => 'Rasm hajmi 2MB dan oshmasligi kerak.',
            'per_page.min' => 'Sahifalash miqdori kamida 10 bo‘lishi kerak.',
            'per_page.max' => 'Sahifalash miqdori 50 dan oshmasligi kerak.',
        ]);
        $data = [
            'per_page' => $request->per_page,
        ];
        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $file = $request->file('avatar');
            $fileName = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $fileName, 'public');
            $data['image'] = asset('storage/' . $path);
        }
        $user->update($data);
        return back()->with('success', 'Sozlamalar muvaffaqiyatli saqlandi!');
    }
}
