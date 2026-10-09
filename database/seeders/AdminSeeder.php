<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Membuat akun admin awal dari ADMIN_PHONE dan ADMIN_PASSWORD (opsional ADMIN_NAME).
 * Dilewati bila salah satunya kosong atau nomor tersebut sudah terdaftar.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $phone = env('ADMIN_PHONE');
        $password = env('ADMIN_PASSWORD');

        if (!$phone || !$password || User::where('phone', $phone)->exists()) {
            return;
        }

        $name = env('ADMIN_NAME', 'Administrator');

        User::create([
            'name' => $name,
            'parent_name' => $name,
            'phone' => $phone,
            'bod' => '1990-01-01',
            'password' => $password,
            'roles' => UserRole::Admin,
        ]);
    }
}
