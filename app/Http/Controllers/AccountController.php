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
        $accounts = Account::paginate(15);
        return view('pages.accounts.index', compact(['accounts']));
    }

    public function create()
    {
        $users = User::where('pos', '!=', 'user')->get();
        return view('pages.accounts.create', compact(['users']));
    }

    public function store(Request $request)
    {
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
        $response = Http::get("https://generativelanguage.googleapis.com/v1beta/models/{$model}?key={$token}");
        if ($response->failed()) {
            return back()->withInput()
                ->withErrors(['token' => 'Kiritilgan API token xato yoki ushbu model (' . $model . ') uchun yaroqsiz! Iltimos tekshirib qayta kiriting.']);
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
        $users = User::where('pos', '!=', 'user')->get();
        return view('pages.accounts.edit', compact(['account', 'users']));
    }

    public function update(Request $request, Account $account)
    {
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
        $department_id = null;
        $user_id = $request->user_id ?? null;
        if ($user_id) {
            $user = User::find($user_id);
            if ($user) {
                $department_id = $user->department_id;
            }
        }
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
        if ($account->lessons->count() > 0) {
            return redirect()->route('accounts.index')
                ->with('error', 'Biriktirilgan fanlar mavjudligi tufayli o‘chirib bo‘lmaydi!');
        }
        $account->delete();
        return redirect()->route('accounts.index')
            ->with('success', 'API akkaunt muvaffaqiyatli o‘chirildi!');
    }
}
