<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'nama' => 'Ary Iswahyudi',
                'user' => 'ary.iswahyudi',
                'password' => Hash::make('password'),
                'wilayah' => null,
                'status' => 1,
                'akses' => null,
                'bidang_id' => null,
                'sub_bidang_id' => null,
                'is_admin' => false,
                'pegawai_id' => null,
            ],
            // ... Tambahkan data user lain sesuai gambar ...
        ];
        DB::table('users')->insert($users);
    }
} 