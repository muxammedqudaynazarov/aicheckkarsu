<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LanguageController extends Controller
{
    public function models()
    {
        $response = Http::get("https://generativelanguage.googleapis.com/v1beta/models?key=AIzaSyCAxihtfRFOMZb4L7vrBkQ-ooofGEW0YIY");
        if ($response->successful()) {
            $models = $response->json();
            foreach ($models['models'] as $model) {
                echo $model["name"] . "<br>";
            }
        } else {
            dd("Xatolik yuz berdi: " . $response->status());
        }
    }
}
