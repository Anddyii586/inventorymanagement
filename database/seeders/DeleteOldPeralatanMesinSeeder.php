<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeleteOldPeralatanMesinSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('golongan_peralatan_mesin')->where('created_at', '<', '2025-01-01')->delete();
    }
}