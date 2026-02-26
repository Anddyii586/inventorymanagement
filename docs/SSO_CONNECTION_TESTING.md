# SSO Connection Testing Guide

Dokumen ini menjelaskan cara melakukan testing koneksi SSO dengan berbagai skenario.

## Fitur Testing

Sistem SSO connection sudah dilengkapi dengan fitur testing yang memungkinkan Anda untuk:
1. **Simulasi koneksi gagal** - Menggunakan IP acak untuk test skenario ketika SSO tidak tersedia
2. **Test dengan IP real** - Menggunakan IP SSO yang sebenarnya untuk verifikasi koneksi berhasil

## Command Testing

### 1. Test dengan IP Acak (Simulasi Gagal)

Untuk mensimulasikan kondisi ketika SSO tidak tersedia (seperti saat VPN mati):

```bash
php artisan sso:test --test-fail --force
```

**Hasil yang diharapkan:**
- ✗ Socket connection failed
- ✗ SSO connection is NOT AVAILABLE
- SSO features akan disabled gracefully

**Kegunaan:**
- Test apakah error handling bekerja dengan baik
- Verifikasi bahwa SSO features otomatis disable saat SSO tidak tersedia
- Pastikan tidak ada timeout error

### 2. Test dengan IP Real (Kondisi Normal)

Untuk test dengan koneksi SSO yang sebenarnya:

```bash
php artisan sso:test --test-real --force
```

**Hasil yang diharapkan:**
- ✓ Socket connection successful
- ✓ SSO connection is AVAILABLE
- SSO features akan bekerja normal

**Kegunaan:**
- Verifikasi koneksi SSO berfungsi normal
- Pastikan SSO features aktif saat koneksi tersedia
- Test setelah VPN dinyalakan

### 3. Test Normal (Menggunakan Config Default)

```bash
php artisan sso:test --force
```

Ini akan menggunakan konfigurasi default dari `config/database.php`.

## Environment Variables untuk Testing

Anda juga bisa menggunakan environment variables untuk testing:

### Di file `.env`:

```env
# Untuk simulasi koneksi gagal
SSO_TEST_HOST=192.168.999.999
SSO_TEST_PORT=3316

# Atau disable SSO sepenuhnya
SSO_DISABLED=true
```

### Via Command Line (Temporary):

```bash
# Test dengan IP acak
SSO_TEST_HOST=192.168.200.200 php artisan sso:test --force

# Kembali ke IP real
unset SSO_TEST_HOST
php artisan sso:test --force
```

## Skenario Testing yang Disarankan

### Skenario 1: Test SSO Features Disable saat SSO Tidak Tersedia

1. Jalankan test dengan IP acak:
   ```bash
   php artisan sso:test --test-fail --force
   ```

2. Clear cache:
   ```bash
   php artisan cache:clear
   ```

3. Coba akses fitur SSO (login, session check, dll)
4. **Expected:** SSO features akan fail gracefully tanpa error timeout

### Skenario 2: Test SSO Features Enable saat SSO Tersedia

1. Pastikan VPN aktif atau koneksi SSO tersedia
2. Jalankan test dengan IP real:
   ```bash
   php artisan sso:test --test-real --force
   ```

3. Clear cache:
   ```bash
   php artisan cache:clear
   ```

4. Coba akses fitur SSO
5. **Expected:** SSO features bekerja normal

### Skenario 3: Test Transisi dari Gagal ke Berhasil

1. Test dengan IP acak (simulasi VPN mati):
   ```bash
   php artisan sso:test --test-fail --force
   ```

2. Coba akses fitur SSO - harus fail gracefully

3. Test dengan IP real (simulasi VPN nyala):
   ```bash
   php artisan sso:test --test-real --force
   php artisan cache:clear
   ```

4. Coba akses fitur SSO lagi
5. **Expected:** SSO features sekarang bekerja

## Fitur SSO yang Terpengaruh

Fitur-fitur berikut menggunakan SSO connection dan akan terpengaruh:

1. **SsoSessionHelper::isSsoConnectionAvailable()** - Check ketersediaan SSO
2. **SsoSessionHelper::checkAndGetUser()** - Auto-login dari SSO session
3. **SsoSessionHelper::logoutSsoSession()** - Logout SSO session
4. **SsoController** - Controller untuk handle SSO redirect
5. **LogoutSsoSession Listener** - Listener untuk logout SSO saat user logout

Semua fitur ini sudah dilengkapi dengan error handling yang baik dan akan fail gracefully jika SSO tidak tersedia.

## Troubleshooting

### Cache Masih Menyimpan Status Lama

Jika SSO masih dianggap tidak tersedia padahal koneksi sudah tersedia:

```bash
php artisan cache:clear
php artisan optimize:clear
php artisan sso:test --test-real --force
```

### Error Timeout Masih Terjadi

Pastikan:
1. Socket check bekerja (timeout 0.5 detik)
2. Cache di-clear setelah koneksi gagal
3. Semua query SSO sudah wrapped dengan try-catch

### SSO Features Tidak Aktif Setelah VPN Dinyalakan

1. Clear cache:
   ```bash
   php artisan cache:clear
   ```

2. Test koneksi:
   ```bash
   php artisan sso:test --test-real --force
   ```

3. Hard refresh browser (Cmd+Shift+R atau Ctrl+Shift+R)

## Catatan Penting

- **Cache Duration:** Default cache adalah 5 menit untuk SSO (lebih lama dari HRD karena SSO check lebih jarang)
- **Socket Timeout:** 0.5 detik untuk mencegah timeout lama
- **Auto Clear:** Cache otomatis di-clear saat koneksi gagal
- **Testing Mode:** Environment variables `SSO_TEST_HOST` dan `SSO_TEST_PORT` hanya untuk testing, tidak akan mempengaruhi production
- **Graceful Degradation:** Semua SSO features akan fail gracefully tanpa mengganggu aplikasi utama

## Production

Di production, pastikan:
- Tidak ada `SSO_TEST_HOST` atau `SSO_TEST_PORT` di `.env`
- `SSO_DISABLED` tidak diset ke `true`
- Koneksi SSO tersedia di server yang sama

