<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'info@code14.nl',
        ], [
            'name' => 'Demo User',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);
    }
}
