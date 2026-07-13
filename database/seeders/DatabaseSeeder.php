<?php

namespace Database\Seeders;

use App\Models\Personne;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Personne::firstOrCreate(
            ['email' => 'superadmin@recrutement.local'],
            [
                'nom'      => 'Admin',
                'prenom'   => 'Super',
                'password' => Hash::make('SuperAdmin@2026'),
                'role'     => 'super_admin',
            ]
        );
    }
}