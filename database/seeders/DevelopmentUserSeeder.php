<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentUserSeeder extends Seeder
{
    /**
     * Run the database seeds for development environment.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->warn('DevelopmentUserSeeder tidak dapat dijalankan di environment produksi.');

            return;
        }

        $adminPassword = config('auth.dev_credentials.admin_password');
        $petugasPassword = config('auth.dev_credentials.petugas_password');

        if (! is_string($adminPassword) || trim($adminPassword) === '' || ! is_string($petugasPassword) || trim($petugasPassword) === '') {
            $this->command->error('Seeder dihentikan: DEV_ADMIN_PASSWORD dan DEV_PETUGAS_PASSWORD wajib diatur pada file .env dan tidak boleh kosong.');

            return;
        }

        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator Sistem',
                'nama' => 'Administrator Sistem',
                'role' => UserRole::Admin->value,
                'email' => 'admin@sistem-data-wna.local',
                'password' => Hash::make($adminPassword),
            ]
        );

        if ($admin->wasRecentlyCreated) {
            $this->command->info('Akun admin berhasil dibuat.');
        } else {
            $this->command->line('Akun admin sudah ada, data tidak diubah.');
        }

        $petugas = User::firstOrCreate(
            ['username' => 'petugas'],
            [
                'name' => 'Petugas Sistem',
                'nama' => 'Petugas Sistem',
                'role' => UserRole::User->value,
                'email' => 'petugas@sistem-data-wna.local',
                'password' => Hash::make($petugasPassword),
            ]
        );

        if ($petugas->wasRecentlyCreated) {
            $this->command->info('Akun petugas berhasil dibuat.');
        } else {
            $this->command->line('Akun petugas sudah ada, data tidak diubah.');
        }
    }
}
