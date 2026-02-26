# Migrasi Kode Wilayah

Dokumen ini menjelaskan tentang migrasi kode wilayah yang dilakukan untuk memperbaiki kesalahan pada basis data.

## Latar Belakang

Terdapat kesalahan pada tabel `struktur_wilayah` dimana:
- Mataram seharusnya memiliki kode `02` (bukan `01`)
- Lombok Barat seharusnya memiliki kode `01` (bukan `02`)

Perubahan ini juga berpengaruh terhadap kolom `kode_lokasi` pada aset yang menggunakan kode wilayah tersebut.

## Perubahan yang Dilakukan

### 1. Update Kode Wilayah
- Mataram: `01` → `02`
- Lombok Barat: `02` → `01`

### 2. Update Kode Lokasi Aset
Kode lokasi pada aset berikut akan diperbarui secara otomatis:
- Peralatan Mesin (`golongan_peralatan_mesin`)
- Tanah (`golongan_tanah`)

**Catatan**: Update kode_lokasi dilakukan menggunakan query builder langsung untuk menghindari perubahan pada kolom `updated_at` dan `created_at`. Service juga menyediakan method alternatif menggunakan `withoutTimestamps()` jika diperlukan.

## Cara Penggunaan

### 1. Menggunakan Artisan Command

#### Preview Perubahan (Tanpa Melakukan Migrasi)
```bash
php artisan wilayah:migrate --preview
```

#### Jalankan Migrasi
```bash
php artisan wilayah:migrate
```

### 2. Menggunakan Web Interface

#### Preview Perubahan
Akses: `http://your-domain/wilayah-migration/preview`

#### Jalankan Migrasi
Klik tombol "Jalankan Migrasi" pada halaman preview.

## Struktur Kode Lokasi

Kode lokasi mengikuti format:
```
{wilayah_id}.{direktorat_id}.{bidang_id}.{sub_bidang_id}.{unit_id}.{tahun}
```

Contoh: `02.01.01.01.01.24` (Mataram, Direktorat 01, Bidang 01, Sub Bidang 01, Unit 01, Tahun 2024)

## File yang Terlibat

### Service
- `app/Services/WilayahMigrationService.php` - Service utama untuk migrasi

### Command
- `app/Console/Commands/MigrateWilayahCodes.php` - Artisan command

### Controller
- `app/Http/Controllers/WilayahMigrationController.php` - Controller untuk web interface

### View
- `resources/views/wilayah-migration/preview.blade.php` - Halaman preview

### Routes
- `routes/web.php` - Route untuk web interface

## Implementasi Teknis

### Update Tanpa Timestamps
Service menggunakan query builder langsung untuk update kode_lokasi:

```php
// Untuk peralatan mesin
DB::table('golongan_peralatan_mesin')
    ->where('id', $aset->id)
    ->update(['kode_lokasi' => $newKodeLokasi]);

// Untuk tanah
DB::table('golongan_tanah')
    ->where('id', $aset->id)
    ->update(['kode_lokasi' => $newKodeLokasi]);
```

Alternatif menggunakan Eloquent dengan `withoutTimestamps()`:
```php
PeralatanMesin::withoutTimestamps(function () use ($id, $newKodeLokasi) {
    PeralatanMesin::where('id', $id)->update(['kode_lokasi' => $newKodeLokasi]);
});
```

## Keamanan

- Migrasi menggunakan database transaction untuk memastikan konsistensi data
- Jika terjadi error, semua perubahan akan di-rollback
- Log aktivitas migrasi disimpan di `storage/logs/laravel.log`
- Update kode_lokasi dilakukan tanpa mengubah kolom `updated_at` pada tabel aset

## Backup

**PENTING**: Sebelum menjalankan migrasi, pastikan untuk melakukan backup database terlebih dahulu.

```bash
# Backup database
mysqldump -u username -p database_name > backup_before_migration.sql
```

## Troubleshooting

### Error: Foreign Key Constraint
Jika terjadi error foreign key constraint, pastikan:
1. Semua aset memiliki data wilayah yang valid
2. Tidak ada referensi ke kode wilayah yang akan diubah

### Error: Duplicate Entry
Jika terjadi error duplicate entry, pastikan:
1. Tidak ada konflik kode wilayah setelah pertukaran
2. Semua data wilayah sudah benar

## Monitoring

Setelah migrasi, periksa:
1. Data wilayah di tabel `struktur_wilayah`
2. Kode lokasi pada aset peralatan mesin dan tanah
3. Log file untuk memastikan tidak ada error

## Rollback

Jika diperlukan rollback, gunakan backup database yang telah dibuat sebelum migrasi.

```bash
# Restore database
mysql -u username -p database_name < backup_before_migration.sql
``` 