<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'owner@gestorpro.test'],
            ['name' => 'Owner Demo', 'password' => 'password123', 'role' => 'owner']
        );

        User::updateOrCreate(
            ['email' => 'admin@gestorpro.test'],
            ['name' => 'Admin Demo', 'password' => 'password123', 'role' => 'admin']
        );

        User::updateOrCreate(
            ['email' => 'member@gestorpro.test'],
            ['name' => 'Member Demo', 'password' => 'password123', 'role' => 'member']
        );
    }
}
