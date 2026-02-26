# Dokumentasi Integrasi SSO

## Masalah Cross-Domain Cookies

Browser **tidak bisa** membaca cookies dari domain lain karena **Same-Origin Policy**. Ketika user sudah login di `http://app.ptamgm.net/`, cookies session mereka **tidak akan** dikirim ke aplikasi ini secara otomatis.

## Solusi yang Benar

SSO (`http://app.ptamgm.net/`) harus mengirim data user melalui **URL parameter** saat redirect ke callback URL.

### Flow yang Benar:

1. User klik "Login dengan SSO" → Redirect ke `http://app.ptamgm.net/?callback=YOUR_CALLBACK_URL`
2. User login di SSO (jika belum login)
3. **SSO harus redirect ke callback URL dengan data user:**
   ```
   http://your-app.com/sso/callback?nik=201103266&uid=201103266&nama=User Name
   ```
4. Aplikasi membaca NIK/UID dari URL parameter
5. Auto-login user berdasarkan NIK/UID

## Yang Perlu Dikonfigurasi di SSO

SSO (`http://app.ptamgm.net/`) perlu:

1. **Menerima parameter `callback`** saat user mengakses:
   ```
   http://app.ptamgm.net/?callback=http://your-app.com/sso/callback
   ```

2. **Setelah user login**, redirect ke callback URL dengan data user:
   ```php
   // Di aplikasi SSO, setelah login berhasil:
   $callbackUrl = $_GET['callback'];
   $user = Auth::user(); // atau session user
   
   $redirectUrl = $callbackUrl . '?' . http_build_query([
       'nik' => $user->nik,
       'uid' => $user->uid ?? $user->nik,
       'nama' => $user->nama,
       // tambahkan data lain jika diperlukan
   ]);
   
   return redirect($redirectUrl);
   ```

3. **Alternatif: Menggunakan Token**
   - SSO bisa generate token sementara
   - Redirect dengan token: `callback?token=ABC123`
   - Aplikasi ini memanggil API SSO untuk verify token dan dapatkan user data

## Testing

1. Pastikan SSO sudah dikonfigurasi untuk mengirim data via URL parameter
2. Klik "Login dengan SSO" di aplikasi ini
3. Login di SSO
4. SSO harus redirect kembali dengan data user di URL
5. Aplikasi akan auto-login user

## Catatan Keamanan

- Jangan kirim password melalui URL parameter
- Gunakan HTTPS untuk production
- Pertimbangkan menggunakan signed token untuk verifikasi
- Validasi data yang diterima dari SSO

