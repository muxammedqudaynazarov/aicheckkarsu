<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LanguageController extends Controller
{
    public function models()
    {
        $response = Http::get("https://generativelanguage.googleapis.com/v1beta/models?key=" . env('GEMINI_API_KEY'));
        if ($response->successful()) {
            $models = $response->json();
            dd($models);
        } else {
            dd("Xatolik yuz berdi: " . $response->status());
        }
    }
}
