<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds idempotently.
     */
    public function run(): void
    {
        $defaultSettings = [
            'app_name' => 'Sistem Data WNA Kanim Ponorogo',
            'instansi_name' => 'Kantor Imigrasi Kelas II Non TPI Ponorogo',
            'app_description' => 'Sistem Informasi Data Warga Negara Asing',
        ];

        foreach ($defaultSettings as $key => $defaultValue) {
            if (! Setting::query()->where('key', $key)->exists()) {
                Setting::create([
                    'key' => $key,
                    'value' => $defaultValue,
                ]);
            }
        }
    }
}
