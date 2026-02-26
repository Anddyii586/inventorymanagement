<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WilayahMigrationService;

class MigrateWilayahCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wilayah:migrate {--preview : Preview perubahan tanpa melakukan migrasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi kode wilayah: Mataram (01->02), Lombok Barat (02->01) dan update kode_lokasi pada aset';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('preview')) {
            $this->previewMigration();
        } else {
            $this->performMigration();
        }
    }
    
    /**
     * Preview perubahan yang akan dilakukan
     */
    private function previewMigration()
    {
        $this->info('Preview perubahan migrasi kode wilayah...');
        $this->newLine();
        
        $preview = WilayahMigrationService::previewMigration();
        
        // Tampilkan perubahan wilayah
        if (!empty($preview['wilayah_changes'])) {
            $this->info('Perubahan Kode Wilayah:');
            $this->table(
                ['Kode Lama', 'Kode Baru', 'Nama Wilayah'],
                collect($preview['wilayah_changes'])->map(function ($change) {
                    return [$change['old_code'], $change['new_code'], $change['wilayah']];
                })
            );
        } else {
            $this->warn('Tidak ada perubahan kode wilayah yang akan dilakukan.');
        }
        
        $this->newLine();
        
        // Tampilkan perubahan peralatan mesin
        if (!empty($preview['peralatan_mesin_changes'])) {
            $this->info('Perubahan Kode Lokasi Peralatan Mesin:');
            $this->table(
                ['ID Aset', 'Kode Lokasi Lama', 'Kode Lokasi Baru', 'Wilayah'],
                collect($preview['peralatan_mesin_changes'])->take(10)->map(function ($change) {
                    return [$change['id'], $change['old_kode_lokasi'], $change['new_kode_lokasi'], $change['wilayah']];
                })
            );
            
            if (count($preview['peralatan_mesin_changes']) > 10) {
                $this->warn('Menampilkan 10 dari ' . count($preview['peralatan_mesin_changes']) . ' perubahan peralatan mesin.');
            }
        } else {
            $this->warn('Tidak ada perubahan kode lokasi peralatan mesin yang akan dilakukan.');
        }
        
        $this->newLine();
        
        // Tampilkan perubahan tanah
        if (!empty($preview['tanah_changes'])) {
            $this->info('Perubahan Kode Lokasi Tanah:');
            $this->table(
                ['ID Aset', 'Kode Lokasi Lama', 'Kode Lokasi Baru', 'Wilayah'],
                collect($preview['tanah_changes'])->take(10)->map(function ($change) {
                    return [$change['id'], $change['old_kode_lokasi'], $change['new_kode_lokasi'], $change['wilayah']];
                })
            );
            
            if (count($preview['tanah_changes']) > 10) {
                $this->warn('Menampilkan 10 dari ' . count($preview['tanah_changes']) . ' perubahan tanah.');
            }
        } else {
            $this->warn('Tidak ada perubahan kode lokasi tanah yang akan dilakukan.');
        }
        
        $this->newLine();
        $this->info('Untuk menjalankan migrasi, gunakan: php artisan wilayah:migrate');
    }
    
    /**
     * Lakukan migrasi
     */
    private function performMigration()
    {
        $this->info('Memulai migrasi kode wilayah...');
        
        if (!$this->confirm('Apakah Anda yakin ingin melakukan migrasi kode wilayah? Ini akan mengubah data di database.')) {
            $this->warn('Migrasi dibatalkan.');
            return;
        }
        
        $result = WilayahMigrationService::migrateWilayahCodes();
        
        if ($result['success']) {
            $this->info('✅ ' . $result['message']);
        } else {
            $this->error('❌ ' . $result['message']);
        }
    }
} 