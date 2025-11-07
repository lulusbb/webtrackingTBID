<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        // contoh isi awal threshold KPI
        $kpiThresholds = [
            'klien_survei' => [
                'red_max'    => 9,   // <10
                'yellow_min' => 10,  // 10–20
                'yellow_max' => 20,
                'green_min'  => 21,  // >20
            ],
            'klien_masuk' => [
                'red_max'    => 4,
                'yellow_min' => 5,
                'yellow_max' => 10,
                'green_min'  => 11,
            ],
        ];

        Setting::updateOrCreate(
            ['key' => 'kpi_thresholds'],
            ['value' => $kpiThresholds]
        );

        // contoh setting lain kalau dibutuhkan
        // Setting::updateOrCreate(
        //     ['key' => 'company_profile'],
        //     ['value' => ['brand' => 'TBID', 'theme' => 'dark']]
        // );
    }
}
