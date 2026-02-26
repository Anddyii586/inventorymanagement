# HRD Connection Testing Guide

Dokumen ini menjelaskan cara melakukan testing koneksi HRD dengan berbagai skenario.

## Fitur Testing

Sistem HRD connection sudah dilengkapi dengan fitur testing yang memungkinkan Anda untuk:
1. **Simulasi koneksi gagal** - Menggunakan IP acak untuk test skenario ketika HRD tidak tersedia
2. **Test dengan IP real** - Menggunakan IP HRD yang sebenarnya untuk verifikasi koneksi berhasil

## Command Testing

### 1. Test dengan IP Acak (Simulasi Gagal)

Untuk mensimulasikan kondisi ketika HRD tidak tersedia (seperti saat VPN mati):

```bash
php artisan hrd:test --test-fail --force
```

**Hasil yang diharapkan:**
- ✗ Socket connection failed
- ✗ HRD connection is NOT AVAILABLE
- Feature akan disabled di UserResource

**Kegunaan:**
- Test apakah error handling bekerja dengan baik
- Verifikasi bahwa fitur otomatis disable saat HRD tidak tersedia
- Pastikan tidak ada timeout error

### 2. Test dengan IP Real (Kondisi Normal)

Untuk test dengan koneksi HRD yang sebenarnya:

```bash
php artisan hrd:test --test-real --force
```

**Hasil yang diharapkan:**
- ✓ Socket connection successful
- ✓ HRD connection is AVAILABLE
- Feature akan enabled di UserResource

**Kegunaan:**
- Verifikasi koneksi HRD berfungsi normal
- Pastikan fitur aktif saat koneksi tersedia
- Test setelah VPN dinyalakan

### 3. Test Normal (Menggunakan Config Default)

```bash
php artisan hrd:test --force
```

Ini akan menggunakan konfigurasi default dari `config/database.php`.

## Environment Variables untuk Testing

Anda juga bisa menggunakan environment variables untuk testing:

### Di file `.env`:

```env
# Untuk simulasi koneksi gagal
HRD_TEST_HOST=192.168.999.999
HRD_TEST_PORT=3316

# Atau disable HRD sepenuhnya
HRD_DISABLED=true
```

### Via Command Line (Temporary):

```bash
# Test dengan IP acak
HRD_TEST_HOST=192.168.200.200 php artisan hrd:test --force

# Kembali ke IP real
unset HRD_TEST_HOST
php artisan hrd:test --force
```

## Skenario Testing yang Disarankan

### Skenario 1: Test Fitur Disable saat HRD Tidak Tersedia

1. Jalankan test dengan IP acak:
   ```bash
   php artisan hrd:test --test-fail --force
   ```

2. Clear cache:
   ```bash
   php artisan cache:clear
   ```

3. Buka form edit user di browser
4. **Expected:** Field "Pegawai" harus disabled dengan pesan helper

### Skenario 2: Test Fitur Enable saat HRD Tersedia

1. Pastikan VPN aktif atau koneksi HRD tersedia
2. Jalankan test dengan IP real:
   ```bash
   php artisan hrd:test --test-real --force
   ```

3. Clear cache:
   ```bash
   php artisan cache:clear
   ```

4. Buka form edit user di browser
5. **Expected:** Field "Pegawai" harus enabled dan bisa digunakan

### Skenario 3: Test Transisi dari Gagal ke Berhasil

1. Test dengan IP acak (simulasi VPN mati):
   ```bash
   php artisan hrd:test --test-fail --force
   ```

2. Buka form - field harus disabled

3. Test dengan IP real (simulasi VPN nyala):
   ```bash
   php artisan hrd:test --test-real --force
   php artisan cache:clear
   ```

4. Refresh halaman form
5. **Expected:** Field "Pegawai" sekarang enabled

## Troubleshooting

### Cache Masih Menyimpan Status Lama

Jika fitur masih disabled padahal koneksi sudah tersedia:

```bash
php artisan cache:clear
php artisan optimize:clear
php artisan hrd:test --test-real --force
```

### Error Timeout Masih Terjadi

Pastikan:
1. Socket check bekerja (timeout 0.5 detik)
2. Cache di-clear setelah koneksi gagal
3. Double check di closure `options()` berfungsi

### Fitur Tidak Aktif Setelah VPN Dinyalakan

1. Clear cache:
   ```bash
   php artisan cache:clear
   ```

2. Test koneksi:
   ```bash
   php artisan hrd:test --test-real --force
   ```

3. Hard refresh browser (Cmd+Shift+R atau Ctrl+Shift+R)

## Catatan Penting

- **Cache Duration:** Default cache adalah 1 menit untuk lebih responsif
- **Socket Timeout:** 0.5 detik untuk mencegah timeout lama
- **Auto Clear:** Cache otomatis di-clear saat koneksi gagal
- **Testing Mode:** Environment variables `HRD_TEST_HOST` dan `HRD_TEST_PORT` hanya untuk testing, tidak akan mempengaruhi production

## Production

Di production, pastikan:
- Tidak ada `HRD_TEST_HOST` atau `HRD_TEST_PORT` di `.env`
- `HRD_DISABLED` tidak diset ke `true`
- Koneksi HRD tersedia di server yang sama

