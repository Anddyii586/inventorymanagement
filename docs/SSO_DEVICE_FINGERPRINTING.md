# SSO Device Fingerprinting

## Overview

Fitur device fingerprinting meningkatkan keamanan dan akurasi matching session SSO dengan menggunakan identitas device yang lebih spesifik daripada hanya `user_agent` saja.

## Masalah Sebelumnya

Sebelumnya, sistem SSO hanya menggunakan `user_agent` untuk matching session. Masalahnya:
- `user_agent` bisa sama di browser yang berbeda pada device yang berbeda
- `user_agent` bisa di-spoof dengan mudah
- Tidak cukup spesifik untuk mengidentifikasi device tertentu

## Solusi: Device Fingerprinting

Sistem sekarang menggunakan kombinasi beberapa atribut browser untuk membuat fingerprint yang lebih unik:

### Komponen Fingerprint

1. **User Agent** - Browser dan OS information
2. **Screen Properties** - Resolution, color depth, pixel depth
3. **Viewport** - Window dimensions
4. **Timezone** - Timezone dan offset
5. **Language** - Browser language settings
6. **Platform** - OS platform
7. **Hardware** - CPU cores, device memory
8. **Canvas Fingerprint** - Unique rendering characteristics
9. **WebGL Fingerprint** - GPU information
10. **Fonts** - Available fonts on device

### Cara Kerja

1. **Generate Fingerprint** (Client-side)
   - Saat halaman dimuat, JavaScript generate fingerprint dari kombinasi atribut browser
   - Fingerprint di-hash menjadi string unik
   - Disimpan di `localStorage` untuk reuse

2. **Kirim ke Server**
   - Fingerprint dikirim ke endpoint `/sso/device-fingerprint` via POST
   - Disimpan di session Laravel

3. **Matching Session**
   - Saat mencari session SSO yang cocok, sistem menggunakan:
     - `user_agent` (wajib - untuk backward compatibility)
     - `device_fingerprint` (jika tersedia - untuk akurasi lebih tinggi)
   - Jika fingerprint tidak match, session di-skip (lebih aman)

## Implementasi

### File yang Terlibat

1. **`resources/js/device-fingerprint.js`**
   - JavaScript class untuk generate dan manage fingerprint
   - Auto-send fingerprint saat halaman dimuat

2. **`app/Http/Controllers/SsoController.php`**
   - Method `storeDeviceFingerprint()` untuk menerima fingerprint dari client

3. **`app/Helpers/SsoSessionHelper.php`**
   - Method `findUserFromActiveSessions()` dimodifikasi untuk menggunakan fingerprint
   - Matching lebih ketat dengan kombinasi user_agent + fingerprint

4. **Routes**
   - `POST /sso/device-fingerprint` - Endpoint untuk menyimpan fingerprint

### Flow Matching Session

```
1. User mengakses aplikasi
   ↓
2. JavaScript generate fingerprint (jika belum ada di localStorage)
   ↓
3. Fingerprint dikirim ke server dan disimpan di session Laravel
   ↓
4. Middleware CheckSsoSession mencoba match session SSO
   ↓
5. SsoSessionHelper.findUserFromActiveSessions():
   - Ambil semua session aktif dari SSO (last 30 minutes)
   - Filter berdasarkan user_agent (wajib)
   - Jika fingerprint tersedia, filter juga berdasarkan fingerprint
   - Decode session dan extract UID
   - Cari user di database lokal
   ↓
6. Jika user ditemukan → Auto-login
```

## Keamanan

### Keuntungan

1. **Lebih Spesifik**: Fingerprint lebih unik daripada user_agent saja
2. **Lebih Aman**: Sulit untuk di-spoof karena menggunakan banyak atribut
3. **Backward Compatible**: Jika fingerprint tidak tersedia, sistem tetap menggunakan user_agent

### Keterbatasan

1. **Tidak 100% Unik**: Masih mungkin ada collision (dua device dengan fingerprint sama)
2. **Dapat Berubah**: Fingerprint bisa berubah jika:
   - User mengubah resolusi layar
   - User mengubah timezone
   - Browser di-update
   - OS di-update
3. **Privacy**: Mengumpulkan informasi device (tapi tidak mengidentifikasi user secara langsung)

## Konfigurasi

Tidak ada konfigurasi khusus yang diperlukan. Fitur ini aktif secara default jika:
- SSO connection tersedia
- JavaScript enabled di browser
- localStorage tersedia

## Testing

### Manual Testing

1. Buka aplikasi di browser
2. Buka Developer Console (F12)
3. Cek apakah fingerprint dikirim:
   ```javascript
   // Di console, cek localStorage
   localStorage.getItem('device_fingerprint')
   ```
4. Cek Network tab untuk request ke `/sso/device-fingerprint`
5. Cek log Laravel untuk melihat matching process:
   ```
   tail -f storage/logs/laravel.log | grep "SSO Helper"
   ```

### Expected Behavior

- Fingerprint di-generate saat pertama kali mengakses halaman
- Fingerprint disimpan di localStorage
- Fingerprint dikirim ke server dan disimpan di session
- Saat matching session, sistem menggunakan fingerprint untuk filtering tambahan
- Jika fingerprint tidak tersedia, sistem tetap berfungsi dengan user_agent saja

## Troubleshooting

### Fingerprint tidak dikirim

**Kemungkinan penyebab:**
- JavaScript disabled
- localStorage tidak tersedia
- CSRF token tidak valid
- Network error

**Solusi:**
- Cek browser console untuk error
- Pastikan JavaScript enabled
- Cek Network tab untuk request error

### Matching tidak bekerja

**Kemungkinan penyebab:**
- Fingerprint tidak tersedia di session
- Fingerprint tidak match (device berubah)
- SSO connection tidak tersedia

**Solusi:**
- Cek log untuk detail matching process
- Pastikan SSO connection tersedia
- Clear localStorage dan coba lagi

## Catatan Penting

1. **Fingerprint disimpan di session Laravel**, bukan di database SSO
2. **Fingerprint digunakan untuk filtering tambahan**, bukan sebagai satu-satunya cara matching
3. **User_agent tetap wajib** untuk backward compatibility
4. **Fingerprint bisa berubah** jika device/browser berubah, tapi ini normal

## Future Improvements

1. **Penyimpanan di Database**: Simpan fingerprint di database untuk tracking lebih baik
2. **Fingerprint di Session SSO**: Jika memungkinkan, simpan fingerprint di session SSO juga
3. **Fingerprint Versioning**: Track perubahan fingerprint untuk analisis
4. **Machine Learning**: Gunakan ML untuk detect anomaly berdasarkan fingerprint

