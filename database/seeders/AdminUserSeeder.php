<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        Admin::create([
            'name' => env('ADMIN_NAME', '管理员'),
            'email' => env('ADMIN_EMAIL', 'admin@yourdomain.com'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password123')),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
} 