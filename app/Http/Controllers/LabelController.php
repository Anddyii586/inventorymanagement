<?php

namespace App\Http\Controllers;

use App\Models\Tanah;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LabelController extends Controller
{
    public function downloadTanahQrCode($id)
    {
        $tanah = Tanah::findOrFail($id);
        $url = route('public.tanah.detail', $id);

        // Generate QR Code
        $qrcode = QrCode::format('png')
            ->size(300)
            ->errorCorrection('H')
            ->generate($url);

        if (request()->has('with_label')) {
            // Create a new image with both label and QR code
            $manager = new ImageManager(new Driver());
            $img = $manager->create(800, 300)->fill('#ffffff');

            // Add logo
            $logo = $manager->read(public_path('images/logo.png'));
            $logo->resize(120, null);
            $img->place($logo, 'left', 20, 20);

            // Add text
            $img->text('TANAH/BANGUNAN :', 180, 50, function ($font) {
                $font->size(24);
                $font->color('#000000');
                $font->align('left');
                $font->valign('top');
            });

            // Add kode lokasi with dividers
            $img->drawLine(function ($draw) {
                $draw->from(180, 90)->to(580, 90);
                $draw->color('#666666');
                $draw->width(2);
            });
            $img->drawLine(function ($draw) {
                $draw->from(180, 120)->to(580, 120);
                $draw->color('#666666');
                $draw->width(2);
            });

            // Add kode lokasi text
            $x = 180;
            foreach (str_split($tanah->kode_lokasi) as $char) {
                $img->text($char, $x, 110, function ($font) {
                    $font->size(20);
                    $font->color('#000000');
                });
                $x += 25;
            }

            // Add kode aset with dividers
            $img->drawLine(function ($draw) {
                $draw->from(180, 150)->to(580, 150);
                $draw->color('#666666');
                $draw->width(2);
            });
            $img->drawLine(function ($draw) {
                $draw->from(180, 180)->to(580, 180);
                $draw->color('#666666');
                $draw->width(2);
            });

            // Add kode aset text
            $x = 180;
            foreach (str_split($tanah->id) as $char) {
                $img->text($char, $x, 170, function ($font) {
                    $font->size(20);
                    $font->color('#000000');
                });
                $x += 25;
            }

            // Add QR code
            $qr = $manager->read(base64_encode($qrcode));
            $qr->resize(200, 200);
            $img->place($qr, 'right', 20, 50);

            $filename = "label-tanah-{$id}.png";
            $img->save(storage_path("app/public/{$filename}"));
        } else {
            // Just save the QR code
            $filename = "qrcode-tanah-{$id}.png";
            Storage::disk('public')->put($filename, $qrcode);
        }

        return response()->download(storage_path("app/public/{$filename}"))->deleteFileAfterSend();
    }
}
