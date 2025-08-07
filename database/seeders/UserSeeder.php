<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'nim_nip' => 'ADMIN001',
            'name' => 'Administrator',
            'email' => 'admin@stikes.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081234567890',
            'program_studi' => null,
        ]);

        // Dosen
        User::create([
            'nim_nip' => '198001011234567890',
            'name' => 'Dr. Ahmad Santoso, M.Kep',
            'email' => 'ahmad.santoso@stikes.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'phone' => '081234567891',
            'program_studi' => 'Keperawatan',
        ]);

        // Mahasiswa
        User::create([
            'nim_nip' => '2024001',
            'name' => 'Siti Nurhaliza',
            'email' => 'siti.nurhaliza@student.stikes.ac.id',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'phone' => '081234567892',
            'program_studi' => 'Keperawatan',
        ]);
    }
}
