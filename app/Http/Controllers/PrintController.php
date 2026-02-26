<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PeralatanMesin;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Artisan;

class PrintController extends Controller
{
    public function directPrint(Request $request)
    {
        $ids = $request->ids;
        $printer = $request->printer ?? 'SATO_CG408';
        
        // Execute the command
        $exitCode = Artisan::call('print:qr-codes', [
            'ids' => $ids,
            '--printer' => $printer
        ]);
        
        $output = Artisan::output();
        
        if ($exitCode === 0) {
            return response()->json([
                'success' => true,
                'message' => 'Print job sent successfully',
                'output' => $output
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to print',
                'output' => $output
            ], 500);
        }
    }
    
    public function generateZPLFile(Request $request)
    {
        $ids = explode(',', $request->ids);
        
        $data = PeralatanMesin::with(['subSubKelompok', 'ruangan', 'wilayah', 'subBidang', 'unit'])
            ->whereIn('id', $ids)
            ->get();
            
        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No assets found'
            ], 404);
        }
        
        $zplCommands = $this->generateZPLCommands($data);
        $zplContent = implode("\n", $zplCommands);
        
        $filename = 'qr_codes_' . date('Y-m-d_H-i-s') . '.zpl';
        $filepath = storage_path('app/print/' . $filename);
        
        // Create directory if not exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        file_put_contents($filepath, $zplContent);
        
        return response()->json([
            'success' => true,
            'message' => 'ZPL file generated successfully',
            'filename' => $filename,
            'filepath' => $filepath,
            'download_url' => route('download.zpl', ['filename' => $filename])
        ]);
    }
    
    private function generateZPLCommands($data)
    {
        $zplCommands = [];
        
        foreach ($data as $record) {
            // Generate QR code as text (not image) for better ZPL compatibility
            $qrCodeData = route('public.peralatan-mesin.detail', $record->id);
            
            // ZPL Command optimized for SATO CG408TT thermal printer
            // Based on standard thermal label dimensions and printer capabilities
            $zpl = "^XA"; // Start ZPL
            $zpl .= "^MMT"; // Print mode: tear off
            $zpl .= "^PW384"; // Print width: 48mm = 384 dots at 203 DPI
            $zpl .= "^LL240"; // Label length: 30mm = 240 dots at 203 DPI
            $zpl .= "^LS0"; // Left margin: 0
            $zpl .= "^BY2"; // Barcode width: 2
            
            // QR Code section (left side - 18mm width)
            $zpl .= "^FO10,10"; // Field origin: QR code position
            $zpl .= "^BQN,2,4"; // QR Code: model 2, magnification 4
            $zpl .= "^FD" . $qrCodeData . "^FS"; // QR Code data
            
            // Text section (right side - 30mm width)
            // Title
            $zpl .= "^FO200,10"; // Text position
            $zpl .= "^A0N,15,15"; // Font: 15x15
            $zpl .= "^FD" . ($record->kode_lokasi ?? 'XX.XX.XX.XX.XX.XX') . "^FS"; // Kode lokasi
            
            // Asset code
            $zpl .= "^FO200,40"; // Next text position
            $zpl .= "^A0N,15,15"; // Font: 15x15
            $zpl .= "^FD" . $record->id . "^FS"; // Kode aset
            
            // Sub kelompok (smaller font)
            $zpl .= "^FO200,70"; // Next text position
            $zpl .= "^A0N,12,12"; // Smaller font: 12x12
            $zpl .= "^FD" . ($record->subSubKelompok->sub_sub_kelompok ?? 'N/A') . "^FS"; // Sub sub kelompok
            
            // Room name if available (smallest font)
            if ($record->ruangan) {
                $zpl .= "^FO200,100"; // Next text position
                $zpl .= "^A0N,10,10"; // Smallest font: 10x10
                $zpl .= "^FD" . $record->ruangan->nama . "^FS"; // Room name
            }
            
            $zpl .= "^XZ"; // End ZPL
            
            $zplCommands[] = $zpl;
        }
        
        return $zplCommands;
    }
    
    public function downloadZPL($filename)
    {
        $filepath = storage_path('app/print/' . $filename);
        
        if (!file_exists($filepath)) {
            abort(404, 'File not found');
        }
        
        return response()->download($filepath, $filename, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}
