<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::create([
            'company_name' => 'PT HRIS Tech',
            'office_location' => 'Jakarta, Indonesia',
            'office_lat' => '-6.175110',
            'office_long' => '106.827153',
            'attendance_radius' => 50,
            'default_time_in' => '09:00:00',
            'default_time_out' => '17:00:00',
            'timezone' => 'Asia/Jakarta'
        ]);
    }
}
