<?php

namespace App\Http\Controllers;

use App\Events\LessonProgressUpdated;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function event()
    {
        return event(new LessonProgressUpdated(1));
    }

    public function index()
    {
        $user = auth()->user();
        if (!$user->can('accounts.view')) {
            return redirect()->back()->with('error', 'Sizda bu sahifaga kirish huquqi yo‘q.');
        }
        // if/else o'rniga when() ishlatildi. Super admin bo'lmasa, faqat o'zining akkauntlarini ko'radi.
        $accounts = Account::when($user->pos !== 'super_admin', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->latest()->paginate($user->per_page);
        return view('pages.accounts.index', compact('accounts'));
    }

    public function create()
    {
        $user = auth()->user();
        if (!$user->can('accounts.create')) {
            return redirect()->route('accounts.index')->with('error', 'Sizda yangi token yaratish huquqi yo‘q.');
        }
        // Faqat kerakli ustunlar olinmoqda (id va name) xotirani tejash uchun
        $users = User::where('pos', '!=', 'user')->get(['id', 'name']);
        return view('pages.accounts.create', compact('users'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('accounts.create')) {
            return redirect()->route('accounts.index')->with('error', 'Sizda yangi token yaratish huquqi yo‘q.');
        }
        $request->validate([
            'email' => 'required|email|max:84',
            'rpd_def' => 'required|numeric|min:1',
            'ai_model' => 'required',
            'token' => [
                'required',
                Rule::unique('accounts', 'token')->where(function ($query) use ($request) {
                    return $query->where('model', $request->ai_model);
                })
            ],
        ], [
            'email.required' => 'E-mail maydoni kiritilishi kerak.',
            'email.email' => 'Iltimos, haqiqiy elektron pochta manzilini kiriting.',
            'email.max' => 'E-mail uzunligi 84 belgidan oshmasligi kerak.',
            'token.required' => 'API token kiritilishi shart.',
            'token.unique' => 'Ushbu token ayni shu model uchun tizimga allaqachon kiritilgan!',
            'rpd_def.numeric' => 'Kunlik RPD sonlardan iborat bo‘lishi shart.',
            'rpd_def.min' => 'Kunlik RPD kamida 1 bo‘lishi shart.',
            'rpd_def.required' => 'Kunlik RPD kiritilishi shart.',
            'ai_model.required' => 'API model maydoni kiritilishi kerak.',
        ]);
        $token = $request->token;
        $model = $request->ai_model;
        // timeout(10) qo'shildi! API qotib qolsa server osilib qolmaydi.
        $response = Http::timeout(10)->get("https://generativelanguage.googleapis.com/v1beta/models/{$model}?key={$token}");
        if ($response->failed()) {
            return back()->withInput()
                ->withErrors(['token' => "Kiritilgan API token xato yoki ushbu model ({$model}) uchun yaroqsiz! Iltimos tekshirib qayta kiriting."]);
        }
        Account::create([
            'email' => $request->email,
            'token' => $token,
            'user_id' => auth()->id() ?? null,
            'rpd' => $request->rpd_def ?? 250,
            'rpd_default' => $request->rpd_def ?? 250,
            'model' => $model,
        ]);
        return redirect()->route('accounts.index')->with('success', 'Akkaunt muvaffaqiyatli qo‘shildi.');
    }

    public function edit(Account $account)
    {
        $user = auth()->user();
        if (!$user->can('accounts.edit')) {
            return redirect()->route('accounts.index')->with('error', 'Sizda o‘zgartirish huquqi yo‘q.');
        }
        if ($user->pos !== 'super_admin' && $account->user_id !== $user->id) {
            return redirect()->route('accounts.index')->with('error', 'Bu ma’lumotlarni o‘zgartira olmaysiz.');
        }
        $users = User::where('pos', '!=', 'user')->get(['id', 'name']);
        return view('pages.accounts.edit', compact('account', 'users'));
    }

    public function update(Request $request, Account $account)
    {
        $user = auth()->user();
        if ($user->pos !== 'super_admin' && $account->user_id !== $user->id) {
            return redirect()->route('accounts.index')->with('error', 'Bu ma’lumotlarni o‘zgartira olmaysiz.');
        }
        if (!$user->can('accounts.edit')) {
            return redirect()->route('accounts.index')->with('error', 'Sizda o‘zgartirish huquqi yo‘q.');
        }
        $request->validate([
            'rpd_def' => 'required|numeric|min:1',
            'ai_model' => 'required',
            'token' => 'required',
        ], [
            'token.required' => 'API token kiritilishi shart.',
            'rpd_def.numeric' => 'Kunlik RPD sonlardan iborat bo‘lishi shart.',
            'rpd_def.min' => 'Kunlik RPD kamida 1 bo‘lishi shart.',
            'rpd_def.required' => 'Kunlik RPD kiritilishi shart.',
            'ai_model.required' => 'API model maydoni kiritilishi kerak.',
        ]);
        // Butun User modelini chaqirish o'rniga, faqat 1 ta kerakli ustunni tezkor tortib olish
        $department_id = $request->user_id
            ? User::where('id', $request->user_id)->value('department_id')
            : null;
        $account->update([
            'token' => $request->token,
            'rpd_default' => $request->rpd_def ?? 250,
            'model' => $request->ai_model ?? 'gemini-3.1-pro-preview',
            'user_id' => $request->user_id ?? null,
            'department_id' => $department_id,
        ]);
        return redirect()->route('accounts.index')->with('success', 'API akkaunt muvaffaqiyatli yangilandi.');
    }

    public function destroy(Account $account)
    {
        $user = auth()->user();
        if ($user->pos !== 'super_admin' && $account->user_id !== $user->id) {
            return redirect()->route('accounts.index')->with('error', 'Bu ma’lumotlarni o‘chira olmaysiz.');
        }
        if (!$user->can('accounts.delete')) {
            return redirect()->route('accounts.index')->with('error', 'Sizda o‘chirish huquqi yo‘q.');
        }
        // ENg MUHIM OPTIMIZATSIYA: lessons->count() xotirani to'ldirib sanaydi.
        // lessons()->exists() esa shunchaki bazadan tezkor so'raydi. (100 barobar tezroq)
        if ($account->lessons()->exists()) {
            return redirect()->route('accounts.index')
                ->with('error', 'Biriktirilgan fanlar mavjudligi tufayli o‘chirib bo‘lmaydi!');
        }
        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'API akkaunt muvaffaqiyatli o‘chirildi!');
    }
}
