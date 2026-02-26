<?php

namespace App\Http\Controllers;

use App\Models\Tanah;
use App\Models\PeralatanMesin;
use App\Models\GedungBangunan;
use App\Models\Jaringan;
use App\Models\AsetTetapLainnya;
use App\Models\KonstruksiDalamPengerjaan;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class PublicAssetController extends Controller
{

    public function show($id)
    {
        // Get prefix from ID (first 2 digits)
        $prefix = substr($id, 0, 2);

        // Redirect based on prefix
        switch($prefix) {
            case '02':
                return redirect()->route('public.tanah.detail', $id);
            case '03':
                return redirect()->route('public.peralatan-mesin.detail', $id);
            case '04':
                return redirect()->route('public.gedung-bangunan.detail', $id);
            case '05':
                return redirect()->route('public.jaringan.detail', $id);
            case '06':
                return redirect()->route('public.aset-tetap-lainnya.detail', $id);
            case '07':
                return redirect()->route('public.konstruksi-dalam-pengerjaan.detail', $id);
            default:
                abort(404);
        }
    }

    public function showTanah($id)
    {
        $tanah = Tanah::with(['wilayah', 'subBidang', 'unit', 'subSubKelompok'])->findOrFail($id);

        // Get images from dokumentasi if exists
        $images = [];
        $documents = [];
        
        if ($tanah->hasDokumentasi()) {
            foreach ($tanah->dokumentasi_files as $file) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $fileUrl = Storage::disk('minio')->url($file);
                
                if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                    $images[] = $fileUrl;
                } else {
                    $documents[] = [
                        'name' => basename($file),
                        'url' => $fileUrl,
                        'type' => $extension
                    ];
                }
            }
        }

        return view('public.tanah.detail', compact('tanah', 'images', 'documents'));
    }

    public function showPeralatanMesin($id)
    {
        $peralatanMesin = PeralatanMesin::with(['wilayah', 'subBidang', 'unit', 'subSubKelompok', 'ruangan'])->findOrFail($id);

        // Get images from dokumentasi if exists
        $images = [];
        $documents = [];
        
        if ($peralatanMesin->hasDokumentasi()) {
            foreach ($peralatanMesin->dokumentasi_files as $file) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $fileUrl = Storage::disk('minio')->url($file);
                
                if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                    $images[] = $fileUrl;
                } else {
                    $documents[] = [
                        'name' => basename($file),
                        'url' => $fileUrl,
                        'type' => $extension
                    ];
                }
            }
        }

        return view('public.peralatan-mesin.detail', compact('peralatanMesin', 'images', 'documents'));
    }

    public function showGedungBangunan($id)
    {
        $gedungBangunan = GedungBangunan::with(['wilayah', 'subBidang', 'unit', 'subSubKelompok'])->findOrFail($id);

        // Get images from dokumentasi if exists
        $images = [];
        $documents = [];
        
        if ($gedungBangunan->hasDokumentasi()) {
            foreach ($gedungBangunan->dokumentasi_files as $file) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $fileUrl = Storage::disk('minio')->url($file);
                
                if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                    $images[] = $fileUrl;
                } else {
                    $documents[] = [
                        'name' => basename($file),
                        'url' => $fileUrl,
                        'type' => $extension
                    ];
                }
            }
        }

        return view('public.gedung-bangunan.detail', compact('gedungBangunan', 'images', 'documents'));
    }

    public function showJaringan($id)
    {
        $jaringan = Jaringan::with(['wilayah', 'subBidang', 'unit', 'subSubKelompok'])->findOrFail($id);

        // Get images from dokumentasi if exists
        $images = [];
        $documents = [];
        
        if ($jaringan->hasDokumentasi()) {
            foreach ($jaringan->dokumentasi_files as $file) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $fileUrl = Storage::disk('minio')->url($file);
                
                if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                    $images[] = $fileUrl;
                } else {
                    $documents[] = [
                        'name' => basename($file),
                        'url' => $fileUrl,
                        'type' => $extension
                    ];
                }
            }
        }

        return view('public.jaringan.detail', compact('jaringan', 'images', 'documents'));
    }

    public function showAsetTetapLainnya($id)
    {
        $asetTetapLainnya = AsetTetapLainnya::with(['wilayah', 'subBidang', 'unit', 'subSubKelompok'])->findOrFail($id);

        // Get images from dokumentasi if exists
        $images = [];
        $documents = [];
        
        if ($asetTetapLainnya->hasDokumentasi()) {
            foreach ($asetTetapLainnya->dokumentasi_files as $file) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $fileUrl = Storage::disk('minio')->url($file);
                
                if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                    $images[] = $fileUrl;
                } else {
                    $documents[] = [
                        'name' => basename($file),
                        'url' => $fileUrl,
                        'type' => $extension
                    ];
                }
            }
        }

        return view('public.aset-tetap-lainnya.detail', compact('asetTetapLainnya', 'images', 'documents'));
    }

    public function showKonstruksiDalamPengerjaan($id)
    {
        $konstruksiDalamPengerjaan = KonstruksiDalamPengerjaan::with(['wilayah', 'subBidang', 'unit', 'subSubKelompok'])->findOrFail($id);

        // Get images from dokumentasi if exists
        $images = [];
        $documents = [];
        
        if ($konstruksiDalamPengerjaan->hasDokumentasi()) {
            foreach ($konstruksiDalamPengerjaan->dokumentasi_files as $file) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $fileUrl = Storage::disk('minio')->url($file);
                
                if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                    $images[] = $fileUrl;
                } else {
                    $documents[] = [
                        'name' => basename($file),
                        'url' => $fileUrl,
                        'type' => $extension
                    ];
                }
            }
        }

        return view('public.konstruksi-dalam-pengerjaan.detail', compact('konstruksiDalamPengerjaan', 'images', 'documents'));
    }
}