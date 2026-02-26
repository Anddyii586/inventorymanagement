<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WilayahMigrationService;

class CheckWilayahStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wilayah:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek status kode wilayah dan statistik migrasi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Memeriksa status kode wilayah...');
        $this->newLine();
        
        $status = WilayahMigrationService::checkMigrationStatus();
        $stats = WilayahMigrationService::getMigrationStats();
        
        // Tampilkan status kode wilayah
        $this->info('📊 Status Kode Wilayah:');
        $this->table(
            ['Wilayah', 'Kode Saat Ini', 'Kode yang Seharusnya'],
            [
                ['Mataram', $status['mataram_code'] ?? 'N/A', '02'],
                ['Lombok Barat', $status['lombok_barat_code'] ?? 'N/A', '01']
            ]
        );
        
        $this->newLine();
        
        // Tampilkan pesan status
        if ($status['needs_migration']) {
            $this->warn('⚠️  ' . $status['message']);
        } else {
            $this->info('✅ ' . $status['message']);
        }
        
        $this->newLine();
        
        // Tampilkan statistik migrasi
        $this->info('📈 Statistik Perubahan yang Akan Dilakukan:');
        $this->table(
            ['Jenis Perubahan', 'Jumlah'],
            [
                ['Wilayah', $stats['wilayah_changes_count']],
                ['Peralatan Mesin', $stats['peralatan_mesin_changes_count']],
                ['Tanah', $stats['tanah_changes_count']],
                ['Total', $stats['total_changes']]
            ]
        );
        
        $this->newLine();
        
        // Tampilkan rekomendasi
        if ($status['needs_migration']) {
            $this->info('💡 Rekomendasi:');
            $this->line('1. Jalankan preview: php artisan wilayah:migrate --preview');
            $this->line('2. Jalankan migrasi: php artisan wilayah:migrate');
        } else {
            $this->info('🎉 Kode wilayah sudah benar, tidak perlu migrasi.');
        }
    }
} 