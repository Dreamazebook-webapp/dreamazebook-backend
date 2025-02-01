<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AiApiKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ai_api_keys')->insert([
            'id' => 1,
            'api_key' => '287c8d5f4705404dae0b3be0e095ca96',
            'current_tasks' => 6,
            'is_active' => 1,
            'created_at' => '2025-01-12 00:58:16',
            'updated_at' => '2025-01-11 17:01:26'
        ]);
    }
} 