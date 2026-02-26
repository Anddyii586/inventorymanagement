<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PeralatanMesin;
use Carbon\Carbon;

class UpdatePeralatanMesinKategoriSeeder extends Seeder
{
    public function run(): void
    {
        // Update data yang sudah ada
        $peralatanMesin = PeralatanMesin::all();
        
        foreach ($peralatanMesin as $item) {
            $updates = [];
            
            // Set kategori berdasarkan merek/tipe
            if (stripos($item->merek, 'toyota') !== false || 
                stripos($item->merek, 'kijang') !== false || 
                stripos($item->merek, 'avanza') !== false ||
                stripos($item->merek, 'innova') !== false ||
                stripos($item->merek, 'mobil') !== false ||
                stripos($item->merek, 'motor') !== false ||
                stripos($item->merek, 'honda') !== false ||
                stripos($item->merek, 'hino') !== false) {
                $updates['kategori'] = 'Kendaraan Dinas';
                $updates['nama_barang'] = $item->merek ?: 'Kendaraan Dinas';
                $updates['nomor_polisi'] = $item->keterangan;
                if ($item->merek == 'Honda GWB80') {
                    $updates['kategori'] = 'Pompa';
                    $updates['nama_barang'] = 'Pompa Sedot Dorong';
                }
            } elseif (stripos($item->tipe, 'pompa') !== false || 
                      stripos($item->merek, 'pompa') !== false) {
                $updates['kategori'] = 'Pompa';
                $updates['nama_barang'] = $item->tipe ?: 'Pompa';
            } else {
                $updates['kategori'] = 'Peralatan';
                $updates['nama_barang'] = $item->tipe ?: 'Peralatan';
            }
            
            // Set tahun pembelian dari tanggal pengadaan
            if ($item->tanggal_pengadaan) {
                $year = Carbon::parse($item->tanggal_pengadaan)->year;
                // Pastikan tahun dalam range yang valid (1950-2025 untuk MySQL YEAR)
                if ($year >= 1950 && $year <= 2025) {
                    $updates['tahun_pembelian'] = $year;
                }
            }
            
            $item->update($updates);
        }
        
        $this->command->info('Updated ' . $peralatanMesin->count() . ' records with categories and tahun pembelian');
    }
} 