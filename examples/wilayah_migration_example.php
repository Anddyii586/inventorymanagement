<?php

/**
 * Contoh Penggunaan WilayahMigrationService
 * 
 * File ini berisi contoh cara menggunakan service migrasi kode wilayah
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\WilayahMigrationService;
use App\Models\PeralatanMesin;
use App\Models\Tanah;

// Contoh 1: Cek status migrasi
echo "=== Cek Status Migrasi ===\n";
$status = WilayahMigrationService::checkMigrationStatus();
print_r($status);

echo "\n=== Statistik Migrasi ===\n";
$stats = WilayahMigrationService::getMigrationStats();
print_r($stats);

// Contoh 2: Preview perubahan
echo "\n=== Preview Perubahan ===\n";
$preview = WilayahMigrationService::previewMigration();

echo "Perubahan Wilayah: " . count($preview['wilayah_changes']) . "\n";
foreach ($preview['wilayah_changes'] as $change) {
    echo "- {$change['wilayah']}: {$change['old_code']} → {$change['new_code']}\n";
}

echo "\nPerubahan Peralatan Mesin: " . count($preview['peralatan_mesin_changes']) . "\n";
foreach (array_slice($preview['peralatan_mesin_changes'], 0, 5) as $change) {
    echo "- ID: {$change['id']}, Kode: {$change['old_kode_lokasi']} → {$change['new_kode_lokasi']}\n";
}

echo "\nPerubahan Tanah: " . count($preview['tanah_changes']) . "\n";
foreach (array_slice($preview['tanah_changes'], 0, 5) as $change) {
    echo "- ID: {$change['id']}, Kode: {$change['old_kode_lokasi']} → {$change['new_kode_lokasi']}\n";
}

// Contoh 3: Jalankan migrasi (uncomment jika ingin menjalankan)
/*
echo "\n=== Jalankan Migrasi ===\n";
$result = WilayahMigrationService::migrateWilayahCodes();
print_r($result);
*/

// Contoh 4: Update manual tanpa timestamps (alternatif)
/*
echo "\n=== Update Manual Tanpa Timestamps ===\n";
use Illuminate\Support\Facades\DB;

// Menggunakan query builder langsung
DB::table('golongan_peralatan_mesin')
    ->where('id', 'some_id')
    ->update(['kode_lokasi' => 'new_kode_lokasi']);

// Menggunakan Eloquent tanpa timestamps
PeralatanMesin::withoutTimestamps(function () {
    PeralatanMesin::where('id', 'some_id')
        ->update(['kode_lokasi' => 'new_kode_lokasi']);
});
*/

echo "\n=== Selesai ===\n"; 