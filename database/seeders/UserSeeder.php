<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'employee_id'    => 'EMP001',
            'name'           => 'Super Admin',
            'role'           => 'SPV',
            'username'       => 'admin',
            'password'       => 'admin123',
            'reset_password' => false,
        ]);
    }
}
