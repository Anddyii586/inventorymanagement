<?php

namespace App\Console\Commands;

use App\Models\PeralatanMesin;
use Illuminate\Console\Command;

class UpdatePeralatanMesinNamaBarang extends Command
{
    protected $signature = 'peralatan-mesin:update-nama-barang';
    protected $description = 'Update nama barang peralatan mesin dengan nama yang lebih spesifik';

    public function handle()
    {
        $this->info('Memulai update nama barang...');
        
        $peralatanMesin = PeralatanMesin::all();
        $updated = 0;
        
        foreach ($peralatanMesin as $item) {
            $newNama = $this->generateNamaBarang($item);
            
            if ($newNama && $newNama !== $item->nama_barang) {
                $item->nama_barang = $newNama;
                $item->save();
                $this->line("Updated: {$item->id} -> {$newNama}");
                $updated++;
            }
        }
        
        $this->info("Selesai! Updated {$updated} records.");
    }
    
    private function generateNamaBarang($item)
    {
        $nama = '';
        $merek = trim($item->merek ?? '');
        $tipe = trim($item->tipe ?? '');
        
        // Untuk Kendaraan Dinas
        if ($item->kategori === 'Kendaraan Dinas') {
            if ($merek) {
                // Jika tipe mengandung merek, gunakan tipe saja
                if ($tipe && stripos($tipe, $merek) !== false) {
                    $nama = $tipe;
                } else {
                    $nama = $merek;
                    if ($tipe && $tipe !== '') {
                        $nama .= ' ' . $tipe;
                    }
                }
            } else {
                $nama = 'Kendaraan Dinas';
            }
        }
        // Untuk Pompa
        elseif ($item->kategori === 'Pompa') {
            if ($tipe) {
                $nama = $tipe;
                if ($merek && stripos($tipe, $merek) === false) {
                    $nama .= ' ' . $merek;
                }
            } else {
                $nama = 'Pompa';
            }
        }
        // Untuk Peralatan
        else {
            if ($tipe) {
                $nama = $tipe;
                if ($merek && stripos($tipe, $merek) === false) {
                    $nama .= ' ' . $merek;
                }
            } elseif ($merek) {
                $nama = $merek;
            } else {
                $nama = 'Peralatan';
            }
        }
        
        return trim($nama);
    }
} 