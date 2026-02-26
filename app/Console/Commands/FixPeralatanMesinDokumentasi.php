<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPeralatanMesinDokumentasi extends Command
{
    protected $signature = 'fix:peralatan-mesin-dokumentasi';
    protected $description = 'Konversi field dokumentasi dari string ke array tanpa mengubah updated_at';

    public function handle()
    {
        // Ambil semua data yang dokumentasi-nya tidak null dan karakter pertama bukan '['
        $items = DB::table('golongan_peralatan_mesin')
            ->whereNotNull('dokumentasi')
            ->whereRaw("LEFT(TRIM(dokumentasi), 1) != '['")
            ->get(['id', 'dokumentasi']);

        $bar = $this->output->createProgressBar($items->count());
        $bar->start();

        foreach ($items as $item) {
            $dokumentasi = trim($item->dokumentasi);
            // Update ke format array JSON
            DB::table('golongan_peralatan_mesin')
                ->where('id', $item->id)
                ->update([
                    'dokumentasi' => json_encode([$dokumentasi])
                ]);
            $bar->advance();
        }

        $bar->finish();
        $this->info(PHP_EOL . 'Selesai mengkonversi dokumentasi yang masih string!');
    }
} 