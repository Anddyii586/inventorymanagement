<?php

namespace App\Console\Commands;

use App\Models\PeralatanMesin;
use App\Services\KodifikasiService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdatePeralatanMesinKodeBarang extends Command
{
    protected $signature = 'peralatan-mesin:update-kode-barang';
    protected $description = 'Update kode barang peralatan mesin berdasarkan tahun pembelian';

    public function handle()
    {
        $this->info('Memulai update kode barang berdasarkan tahun pembelian...');
        
        $peralatanMesin = PeralatanMesin::whereNotNull('tahun_pembelian')->get();
        $updated = 0;
        
        foreach ($peralatanMesin as $item) {
            try {
                // Generate kode barang baru berdasarkan tahun pembelian
                $newKodeBarang = KodifikasiService::kodeBarang(
                    $item, 
                    $item->sub_sub_kelompok_id, 
                    $item->tanggal_pengadaan, 
                    PeralatanMesin::class, 
                    $item->tahun_pembelian
                );
                
                if ($newKodeBarang && $newKodeBarang !== $item->id) {
                    // Check if new ID already exists
                    $existing = PeralatanMesin::where('id', $newKodeBarang)->first();
                    if (!$existing) {
                        $oldId = $item->id;
                        $item->id = $newKodeBarang;
                        $item->save();
                        $this->line("Updated: {$oldId} -> {$newKodeBarang}");
                        $updated++;
                    } else {
                        $this->warn("Skipped: {$item->id} (new ID {$newKodeBarang} already exists)");
                    }
                }
            } catch (\Exception $e) {
                $this->error("Error updating {$item->id}: " . $e->getMessage());
            }
        }
        
        $this->info("Selesai! Updated {$updated} records.");
    }
} 