# Update KIB B Categories - Peralatan dan Mesin

## Overview
Berdasarkan JUKNIS (Petunjuk Teknis) KIB B Peralatan dan Mesin, sistem telah diupdate untuk memisahkan peralatan dan mesin menjadi 3 kategori:

1. **Peralatan** (KIB B Peralatan dan Mesin, 2. Peralatan dan Mesin)
2. **Kendaraan Dinas** (KIB B Peralatan dan Mesin, 1. Kendaraan Dinas)  
3. **Pompa** (KIB B Peralatan dan Mesin, 3. Pompa)

## Perubahan yang Dilakukan

### 1. Database Migration
- **File**: `database/migrations/2025_01_27_000000_add_kib_b_categories_to_peralatan_mesin_table.php`
- **Kolom yang ditambahkan**:
  - `kategori` (enum: Peralatan, Kendaraan Dinas, Pompa)
  - `nama_barang` (string) - kolom nama barang yang diminta user
  - `tahun_pembelian` (year) - kolom tahun pembelian yang diminta user
  - Kolom khusus Kendaraan Dinas: `nomor_pabrik`, `nomor_rangka`, `nomor_mesin`, `nomor_polisi`, `bpkb`
  - Kolom khusus Pompa: `kapasitas_listrik_kwh`, `kapasitas_air`, `head_tekanan`, `merk_panel_pompa`, `type_panel_pompa`, `rtu_ada`, `rtu_tidak_ada`
  - Kolom kelistrikan: `kapasitas_listrik_va`, `slo`, `jil`, `genset`, `panel_listrik_ada`, `panel_listrik_tidak_ada`, `rumah_panel_ada`, `rumah_panel_tidak_ada`
  - `pic` (Person in Charge)

### 2. Model Updates
- **File**: `app/Models/PeralatanMesin.php`
- Menambahkan casts untuk kolom-kolom boolean dan integer baru

### 3. Service Updates
- **File**: `app/Services/KodifikasiService.php`
- Mengupdate method `kodeBarang()` untuk menggunakan `tahun_pembelian` sebagai patokan utama untuk membuat kode barang

### 4. Filament Resource Updates
- **File**: `app/Filament/Resources/PeralatanMesinResource.php`
- Menambahkan form fields untuk semua kolom baru
- Menambahkan conditional sections berdasarkan kategori
- Mengupdate table columns untuk menampilkan kolom baru
- Menambahkan tabs untuk memisahkan kategori (Peralatan, Kendaraan Dinas, Pompa)
- Menambahkan filters untuk tahun pembelian

### 5. Cetak KIR Updates
- **File**: `app/Filament/Resources/PeralatanMesinResource/Pages/ListPeralatanMesin.php`
- Menambahkan checklist untuk memilih kategori KIB B (Peralatan, Kendaraan Dinas, Pompa)
- **File**: `app/Http/Controllers/PeralatanMesinController.php`
- Mengupdate method cetakKIR untuk filter berdasarkan kategori yang dipilih
- **File**: `resources/views/filament/resources/peralatan-mesin-resource/kir-cetak.blade.php`
- Menambahkan informasi kategori yang dipilih di laporan KIR

### 6. Cetak KIB Updates
- **File**: `app/Filament/Resources/PeralatanMesinResource/Pages/ListPeralatanMesin.php`
- Menambahkan radio button untuk memilih kategori KIB B (Peralatan, Kendaraan Dinas, Pompa)
- Menambahkan select dropdown untuk memilih penanggung jawab dari data pengguna (opsional)
- **File**: `app/Http/Controllers/PeralatanMesinController.php`
- Mengupdate method cetakKIB untuk filter berdasarkan kategori yang dipilih
- Menambahkan logic untuk mengambil data penanggung jawab berdasarkan ID yang dipilih
- **File**: `resources/views/filament/resources/peralatan-mesin-resource/kib-cetak.blade.php`
- Menambahkan tabel dinamis sesuai kategori:
  - **Kendaraan Dinas**: 21 kolom (termasuk nomor pabrik, rangka, mesin, polisi, BPKB)
  - **Pompa**: 31 kolom (termasuk kapasitas listrik, air, head tekanan, panel pompa, RTU)
  - **Peralatan**: 16 kolom (standar peralatan)
- Menampilkan nama penanggung jawab yang dipilih atau titik jika tidak dipilih

### 7. Cetak KIB Tanah Updates
- **File**: `app/Filament/Resources/TanahResource/Pages/ListTanah.php`
- Menambahkan select dropdown untuk memilih penanggung jawab dari data pengguna (opsional)
- **File**: `app/Http/Controllers/TanahController.php`
- Menambahkan logic untuk mengambil data penanggung jawab berdasarkan ID yang dipilih
- **File**: `resources/views/filament/resources/tanah-resource/kib-cetak.blade.php`
- Menampilkan nama penanggung jawab yang dipilih atau titik jika tidak dipilih

### 8. Cetak KIR Updates
- **File**: `app/Filament/Resources/PeralatanMesinResource/Pages/ListPeralatanMesin.php`
- Menambahkan select dropdown untuk memilih penanggung jawab dari data pengguna (opsional)
- Menambahkan toggle "Gabungkan Aset yang Sama" dengan checkbox untuk memilih kolom penggabungan
- **File**: `app/Http/Controllers/PeralatanMesinController.php`
- Menambahkan logic untuk mengambil data penanggung jawab berdasarkan ID yang dipilih
- Menambahkan method `groupSimilarAssets()` untuk menggabungkan aset berdasarkan kolom yang dipilih
- Menambahkan method `createGroupingKey()` untuk membuat key penggabungan
- **File**: `resources/views/filament/resources/peralatan-mesin-resource/kir-cetak.blade.php`
- Menampilkan nama penanggung jawab yang dipilih atau titik jika tidak dipilih
- Menampilkan jumlah aset yang digabungkan (kolom jumlah)
- Menampilkan informasi penggabungan di bagian bawah laporan

### 9. Data Migration
- **Seeder**: `database/seeders/UpdatePeralatanMesinKategoriSeeder.php`
- Mengkategorikan data existing berdasarkan merek/tipe
- Mengisi tahun pembelian dari tanggal pengadaan
- Mengisi PIC default

### 10. Commands
- **File**: `app/Console/Commands/UpdatePeralatanMesinKodeBarang.php`
- Command untuk mengupdate kode barang berdasarkan tahun pembelian
- **File**: `app/Console/Commands/UpdatePeralatanMesinNamaBarang.php`
- Command untuk mengupdate nama barang dengan nama yang lebih spesifik

## Hasil Update

### Statistik Data:
- **Kendaraan Dinas**: 31 items
- **Peralatan**: 1,120 items
- **Pompa**: 0 items (akan muncul saat ada data pompa baru)

### Fitur Baru:
1. **Kategori KIB B**: Dropdown untuk memilih kategori (Peralatan/Kendaraan Dinas/Pompa)
2. **Nama Barang**: Kolom untuk nama barang yang lebih deskriptif
3. **Tahun Pembelian**: Kolom terpisah dari tanggal pengadaan
4. **Form Sections**: Section yang muncul sesuai kategori yang dipilih
5. **Kode Barang**: Sekarang menggunakan tahun pembelian sebagai patokan
6. **PIC**: Person in Charge untuk setiap aset
7. **Tabs**: Pemisahan data berdasarkan kategori di halaman list

### Form Sections:
- **Kendaraan Dinas**: Menampilkan fields untuk nomor pabrik, rangka, mesin, polisi, BPKB
- **Pompa**: Menampilkan fields untuk kapasitas listrik, air, head tekanan, panel pompa, RTU
- **Kelistrikan**: Menampilkan fields untuk kapasitas VA, SLO, JIL, genset, panel listrik (untuk Peralatan dan Pompa)

### Tabs Interface:
- **Peralatan**: Menampilkan 1,120 items peralatan
- **Kendaraan Dinas**: Menampilkan 31 items kendaraan
- **Pompa**: Menampilkan items pompa (saat ini 0, akan muncul saat ada data baru)

## Cara Penggunaan

### Menambah Data Baru:
1. Pilih kategori KIB B yang sesuai
2. Isi nama barang
3. Pilih tahun pembelian (akan digunakan untuk kode barang)
4. Isi data sesuai kategori yang dipilih
5. Section form akan muncul sesuai kategori

### Filter Data:
- **Tabs**: Pemisahan data berdasarkan kategori (Peralatan, Kendaraan Dinas, Pompa)
- **Cetak KIR**: Checklist untuk memilih kategori yang akan dicetak + pilihan penanggung jawab + toggle penggabungan aset
- **Cetak KIB**: Radio button untuk memilih kategori dengan tabel dinamis + pilihan penanggung jawab
- **Cetak KIB Tanah**: Pilihan penanggung jawab
- Filter berdasarkan tahun pembelian
- Filter berdasarkan tahun (existing)

### Kode Barang:
- Sekarang menggunakan tahun pembelian sebagai patokan utama
- Format: `[sub_sub_kelompok_id].[tahun_pembelian].[nomor_urut]`
- Contoh: `03.02.01.01.004.20.0001` (tahun 2020)

## Notes
- Data existing telah dikategorikan otomatis berdasarkan merek/tipe
- Kendaraan dengan merek Toyota, Honda, Kijang, dll dikategorikan sebagai "Kendaraan Dinas"
- Data dengan kata "pompa" dikategorikan sebagai "Pompa"
- Sisanya dikategorikan sebagai "Peralatan"
- Tahun pembelian diambil dari tanggal pengadaan untuk data existing 