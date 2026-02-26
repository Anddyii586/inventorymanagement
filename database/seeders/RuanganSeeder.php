<?php

namespace Database\Seeders;

use App\Models\Lokasi\Ruangan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RuanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ruangan::create([
            'unit_id' => '01',
            'kode' => '001',
            'nama' => 'Ruangan Teknologi Informasi',
        ]);
        
        Ruangan::create([
            'unit_id' => '01',
            'kode' => '002',
            'nama' => 'Ruangan Aset',
        ]);
    }
}
