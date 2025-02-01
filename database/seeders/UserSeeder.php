<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'id' => 7,
            'name' => 'Chris',
            'email' => '123@gmail.com',
            'email_verified_at' => null,
            'password' => '$2y$12$VTQpFrCdE/iolD9Zl5wbD.gXcfA6.gRsdcGp.3yVvw/8SR6jbk7RG',
            'remember_token' => 'YhqJDumXujGlYsb87RjdtByFrT6yGI2kKrQ8IHjsC1XIQegAVRJh6GnrRZNp',
            'created_at' => '2025-01-11 15:35:38',
            'updated_at' => '2025-01-11 15:35:38'
        ]);
    }
} 