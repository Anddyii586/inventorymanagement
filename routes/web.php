<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SsoController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\TanahController;
use App\Http\Controllers\PublicAssetController;
use App\Http\Controllers\GedungBangunanController;
use App\Http\Controllers\PeralatanMesinController;
use App\Http\Controllers\WilayahMigrationController;
use App\Http\Controllers\KoreksiPencatatanController;

Route::get('/', function () {
    return redirect('admin');
});

Route::get('/public/{id}', [PublicAssetController::class, 'show'])->name('public.detail');
Route::get('/public/tanah/{id}', [PublicAssetController::class, 'showTanah'])->name('public.tanah.detail');
Route::get('/public/peralatan-mesin/{id}', [PublicAssetController::class, 'showPeralatanMesin'])->name('public.peralatan-mesin.detail');
Route::get('/public/gedung-bangunan/{id}', [PublicAssetController::class, 'showGedungBangunan'])->name('public.gedung-bangunan.detail');
Route::get('/public/jaringan/{id}', [PublicAssetController::class, 'showJaringan'])->name('public.jaringan.detail');
Route::get('/public/aset-tetap-lainnya/{id}', [PublicAssetController::class, 'showAsetTetapLainnya'])->name('public.aset-tetap-lainnya.detail');
Route::get('/public/konstruksi-dalam-pengerjaan/{id}', [PublicAssetController::class, 'showKonstruksiDalamPengerjaan'])->name('public.konstruksi-dalam-pengerjaan.detail');

Route::get('/filament/tanah/{id}/qrcode-download', [LabelController::class, 'downloadTanahQrCode'])->name('filament.resources.tanah.qrcode-download');

Route::get('/peralatan-mesin/cetak-kir', [PeralatanMesinController::class, 'cetakKIR'])->name('peralatan-mesin.cetak-kir');

Route::get('/peralatan-mesin/cetak-kib', [PeralatanMesinController::class, 'cetakKIB'])->name('peralatan-mesin.cetak-kib');
Route::get('/peralatan-mesin/export-kib', [PeralatanMesinController::class, 'exportKIB'])->name('peralatan-mesin.export-kib');
Route::get('/peralatan-mesin/export-kir', [PeralatanMesinController::class, 'exportKIR'])->name('peralatan-mesin.export-kir');
Route::get('/peralatan-mesin/export-rusak-berat', [PeralatanMesinController::class, 'exportRusakBerat'])->name('peralatan-mesin.export-rusak-berat');
Route::get('/peralatan-mesin/cetak-rusak-berat', [PeralatanMesinController::class, 'cetakRusakBerat'])->name('peralatan-mesin.cetak-rusak-berat');
Route::get('/peralatan-mesin/cetak-qr', [PeralatanMesinController::class, 'cetakQR'])->name('peralatan-mesin.cetak-qr');
Route::get('/peralatan-mesin/cetak-qr-zpl', [PeralatanMesinController::class, 'cetakQRZPL'])->name('peralatan-mesin.cetak-qr-zpl');

// Direct printing routes
Route::post('/print/direct', [PrintController::class, 'directPrint'])->name('print.direct');
Route::post('/print/generate-zpl', [PrintController::class, 'generateZPLFile'])->name('print.generate-zpl');
Route::get('/print/download-zpl/{filename}', [PrintController::class, 'downloadZPL'])->name('download.zpl');

Route::get('/assets/print-condition-report', [\App\Http\Controllers\AssetPrintController::class, 'printConditionReport'])->name('assets.print-condition-report');
Route::get('/assets/bulk-print-labels', [\App\Http\Controllers\AssetPrintController::class, 'bulkPrint'])->name('assets.bulk-print-labels');



Route::get('/tanah/cetak-kib', [TanahController::class, 'cetakKIB'])->name('tanah.cetak-kib');
Route::get('/tanah/cetak-idle', [TanahController::class, 'cetakIdle'])->name('tanah.cetak-idle');
Route::get('/tanah/export-kib', [TanahController::class, 'exportKIB'])->name('tanah.export-kib');
Route::get('/tanah/export-idle', [TanahController::class, 'exportIdle'])->name('tanah.export-idle');

Route::get('/gedung-bangunan/cetak-kib', [GedungBangunanController::class, 'cetakKIB'])->name('gedung-bangunan.cetak-kib');
Route::get('/gedung-bangunan/cetak-idle', [GedungBangunanController::class, 'cetakIdle'])->name('gedung-bangunan.cetak-idle');
Route::get('/gedung-bangunan/export-kib', [GedungBangunanController::class, 'exportKIB'])->name('gedung-bangunan.export-kib');
Route::get('/gedung-bangunan/export-idle', [GedungBangunanController::class, 'exportIdle'])->name('gedung-bangunan.export-idle');

// Routes untuk migrasi kode wilayah
Route::get('/wilayah-migration/preview', [WilayahMigrationController::class, 'preview'])->name('wilayah.preview');
Route::post('/wilayah-migration/migrate', [WilayahMigrationController::class, 'migrate'])->name('wilayah.migrate');
Route::get('/wilayah-migration/status', [WilayahMigrationController::class, 'status'])->name('wilayah.status');

// SSO Routes
Route::get('/sso/redirect', [SsoController::class, 'redirect'])->name('sso.redirect');
Route::get('/sso/callback', [SsoController::class, 'callback'])->name('sso.callback');
Route::post('/sso/callback', [SsoController::class, 'callback'])->name('sso.callback.post');
Route::get('/sso/check-session', [SsoController::class, 'checkSession'])->name('sso.check-session');
Route::get('/sso/auto-login', [SsoController::class, 'autoLogin'])->name('sso.auto-login');
Route::post('/sso/auto-login', [SsoController::class, 'autoLogin'])->name('sso.auto-login.post');
Route::post('/sso/device-fingerprint', [SsoController::class, 'storeDeviceFingerprint'])->name('sso.device-fingerprint');

// Koreksi pencatatan (form publik)
Route::post('/koreksi', [KoreksiPencatatanController::class, 'store'])->name('public.koreksi.store');
Route::get('/koreksi', [\App\Http\Controllers\KoreksiController::class, 'index'])->name('public.koreksi.index');
