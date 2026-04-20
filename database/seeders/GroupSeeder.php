<?php

namespace Database\Seeders;

use App\Models\Curriculum;
use App\Models\Department;
use App\Models\Group;
use App\Models\Language;
use App\Models\Specialty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        $faculties = Department::where('structure', '11')->get()->pluck('id')->toArray();
        foreach ($faculties as $faculty) {
            $page = 1;
            do {
                $response = Http::withToken(env('HEMIS_API'))->get('https://student.karsu.uz/rest/v1/data/curriculum-list', [
                    '_department' => $faculty, 'limit' => 200, 'page' => $page
                ]);
                if ($response->failed()) break;
                $curricula = $response->json();
                if (isset($curricula['data']['items'])) {
                    foreach ($curricula['data']['items'] as $curr) {
                        $curriculumName = mb_strtolower($curr['name']);
                        if (str_contains($curriculumName, 'bitirgan') ||
                            str_contains($curriculumName, 'biritgan') ||
                            str_contains($curriculumName, 'bititrgan') ||
                            str_contains($curriculumName, 'bitrgan')) continue;

                        Curriculum::updateOrCreate(
                            ['id' => $curr['id']],
                            [
                                'name' => $curr['name'],
                                'department_id' => $curr['department']['id'] ?? null,
                            ]
                        );
                    }
                }

                $pageCount = $curricula['data']['pagination']['pageCount'] ?? 1;
                $page++;
            } while ($page <= $pageCount);
        }


        foreach ($faculties as $faculty) {
            $page = 1;
            do {
                $response = Http::withToken(env('HEMIS_API'))->get('https://student.karsu.uz/rest/v1/data/group-list', [
                    '_department' => $faculty, 'limit' => 200
                ]);
                $groups = $response->json();
                foreach ($groups['data']['items'] as $group) {
                    $curriculum = Curriculum::where('id', $group['_curriculum'])->exists();
                    if (!$curriculum) continue;

                    $specialty = Specialty::firstOrCreate([
                        'id' => $group['specialty']['id'],
                        'department_id' => $faculty,
                    ], [
                        'name' => $group['specialty']['name'],
                        'code' => $group['specialty']['code'],
                    ]);
                    $lang = Language::firstOrCreate([
                        'id' => $group['educationLang']['code'],
                    ], [
                        'name' => $group['educationLang']['name'],
                    ]);
                    Group::updateOrCreate([
                        'id' => $group['id'],
                        'name' => $group['name'],
                        'specialty_id' => $specialty->id,
                        'language_id' => $lang->id,
                        'curriculum_id' => $group['_curriculum'],
                    ]);
                }

                $pageCount = $groups['data']['pagination']['pageCount'] ?? 1;
                $page++;
            } while ($page <= $pageCount);
        }
    }
}
