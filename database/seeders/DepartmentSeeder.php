<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Department;
use App\Models\Option;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 10; $i <= 18; $i++) {
            $page = 1;
            do {
                $response = Http::withToken(env('HEMIS_API'))->get('https://student.karsu.uz/rest/v1/data/department-list', [
                    '_structure_type' => $i, 'limit' => 200, 'page' => $page
                ]);
                if ($response->failed()) break;
                $resData = $response->json();
                $items = $resData['data']['items'] ?? [];

                foreach ($items as $department) {
                    $parentId = $department['parent'] ?? null;
                    Department::updateOrCreate(
                        ['id' => $department['id']],
                        [
                            'name' => $department['name'],
                            'parent_id' => $parentId,
                            'structure' => $i,
                            'status' => '1',
                        ]
                    );
                }

                $pageCount = $resData['data']['pagination']['pageCount'] ?? 1;
                $page++;
            } while ($page <= $pageCount);
        }

        Account::create([
            'email' => 'm.qudaynazarov@gmail.com',
            'token' => '###',
            'model' => 'gemini-3.1-pro-preview',
            'rpd' => 250,
            'rpd_default' => 250,
        ]);
        Account::create([
            'email' => 'm.qudaynazarov@gmail.com',
            'token' => '###',
            'model' => 'gemini-2.5-pro',
            'rpd' => 1000,
            'rpd_default' => 1000,
        ]);

        Option::create([
            'key' => 'version',
            'value' => '1.1.0'
        ]);
        Option::create([
            'key' => 'creator',
            'value' => 'O.Duysenbaev'
        ]);
        Option::create([
            'key' => 'title',
            'value' => 'AIcheck KarSU'
        ]);
        Option::create([
            'key' => 'description',
            'value' => 'Sun’iy intellektga asoslangan talabalar yozma ishlarini tekshirish va bilimini baholash tizimi'
        ]);
    }
}
