# Dashboard Rekap Peralatan & Mesin

## Overview
Saya telah membuat sistem rekap (summary) untuk golongan peralatan mesin yang ditampilkan langsung di main dashboard. Sistem ini terdiri dari beberapa widget yang memberikan gambaran komprehensif tentang data peralatan dan mesin.

## Widget yang Dibuat

### 1. PeralatanMesinSummaryWidget
Widget statistik yang menampilkan:
- Total Peralatan & Mesin
- Total Nilai Aset
- Rata-rata Nilai per Item
- Kondisi Baik (dengan persentase)
- Kondisi Kurang Baik (dengan persentase)
- Kondisi Rusak Berat (dengan persentase)
- Asal Pembelian (dengan persentase)
- Asal Pengadaan (dengan persentase)

### 2. PeralatanMesinChartWidget
Widget chart donat yang menampilkan:
- Distribusi peralatan dan mesin berdasarkan kategori (sub-sub-kelompok)
- Menampilkan 10 kategori teratas berdasarkan jumlah
- Menggunakan warna yang berbeda untuk setiap kategori

### 3. PeralatanMesinTableWidget
Widget tabel yang menampilkan:
- Kode Kategori
- Nama Kategori
- Jumlah per kategori
- Total Nilai per kategori
- Rata-rata nilai per kategori
- Dapat diurutkan dan dicari
- Pagination untuk data yang banyak

## File yang Dibuat/Dimodifikasi

### Widget Files:
- `app/Filament/Widgets/PeralatanMesinSummaryWidget.php`
- `app/Filament/Widgets/PeralatanMesinChartWidget.php`
- `app/Filament/Widgets/PeralatanMesinTableWidget.php`

### Dashboard Files:
- `app/Filament/Pages/Dashboard.php` (Custom Dashboard)
- `resources/views/filament/pages/dashboard.blade.php`

### Configuration:
- `app/Providers/Filament/AdminPanelProvider.php` (Updated to use custom dashboard)

## Cara Kerja

1. **Summary Widget**: Mengambil data langsung dari tabel `golongan_peralatan_mesin` dan menghitung statistik berdasarkan kondisi, asal usul, dan nilai.

2. **Chart Widget**: Mengelompokkan data berdasarkan `sub_sub_kelompok_id` dan menampilkan distribusi dalam bentuk chart donat.

3. **Table Widget**: Menampilkan data terperinci dengan agregasi berdasarkan kategori, termasuk total nilai dan rata-rata nilai.

## Fitur

- **Real-time Data**: Widget mengambil data langsung dari database
- **Responsive Design**: Widget menyesuaikan dengan ukuran layar
- **Interactive**: Chart dan tabel dapat diinteraksi
- **Comprehensive**: Menampilkan berbagai aspek data peralatan dan mesin
- **User-friendly**: Interface yang mudah dipahami dengan ikon dan warna yang sesuai

## Akses

Dashboard dapat diakses melalui:
- URL: `/admin` (setelah login)
- Widget akan otomatis muncul di halaman utama dashboard

## Data yang Ditampilkan

Widget menampilkan data dari tabel `golongan_peralatan_mesin` dengan relasi ke:
- `asset_sub_sub_kelompok` (untuk kategori)
- Data kondisi: Baik, Kurang Baik, Rusak Berat
- Data asal usul: Pembelian, Pengadaan, Lainnya
- Data nilai: Harga per item dan total nilai

## Maintenance

Widget akan otomatis memperbarui data ketika ada perubahan di database. Tidak diperlukan konfigurasi tambahan untuk pemeliharaan. 