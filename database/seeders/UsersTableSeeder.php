<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@gestorpro.com',
            'password' => Hash::make('Admin123'),
            'role' => 'admin'
        ]);

        User::create([
            'name' => 'Supervisor',
            'email' => 'supervisor@gestorpro.com',
            'password' => Hash::make('Supervisor123'),
            'role' => 'supervisor'
        ]);

        User::create([
            'name' => 'Miembro',
            'email' => 'miembro@gestorpro.com',
            'password' => Hash::make('Member123'),
            'role' => 'member'
        ]);
    }
}
