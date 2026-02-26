<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PeralatanMesin;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class PrintQRCodes extends Command
{
    protected $signature = 'print:qr-codes {ids : Comma separated asset IDs} {--printer= : Printer name or IP address}';
    protected $description = 'Print QR codes directly to thermal printer';

    public function handle()
    {
        $ids = explode(',', $this->argument('ids'));
        $printer = $this->option('printer') ?? 'SATO_CG408';
        
        $this->info("Printing QR codes for assets: " . implode(', ', $ids));
        $this->info("Printer: $printer");
        
        $data = PeralatanMesin::with(['subSubKelompok', 'ruangan', 'wilayah', 'subBidang', 'unit'])
            ->whereIn('id', $ids)
            ->get();
            
        if ($data->isEmpty()) {
            $this->error('No assets found with the provided IDs');
            return 1;
        }
        
        $this->info("Found " . $data->count() . " assets to print");
        
        // Generate ZPL commands
        $zplCommands = $this->generateZPLCommands($data);
        
        // Print using different methods
        $this->printToPrinter($zplCommands, $printer);
        
        $this->info('Print job completed!');
        return 0;
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
    
    private function printToPrinter($zplCommands, $printer)
    {
        $this->info("Attempting to print using multiple methods...");
        
        // Method 1: Using lpr command (Linux/Mac)
        if ($this->printUsingLPR($zplCommands, $printer)) {
            $this->info("Successfully printed using lpr command");
            return;
        }
        
        // Method 2: Using CUPS (Common Unix Printing System)
        if ($this->printUsingCUPS($zplCommands, $printer)) {
            $this->info("Successfully printed using CUPS");
            return;
        }
        
        // Method 3: Direct network printing (if printer has IP)
        if ($this->printUsingNetwork($zplCommands, $printer)) {
            $this->info("Successfully printed using network");
            return;
        }
        
        // Method 4: Save to file for manual printing
        $this->saveToFile($zplCommands);
    }
    
    private function printUsingLPR($zplCommands, $printer)
    {
        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'zpl_');
            file_put_contents($tempFile, implode("\n", $zplCommands));
            
            $command = "lpr -P $printer $tempFile";
            $output = shell_exec($command . " 2>&1");
            
            unlink($tempFile);
            
            return empty($output) || strpos($output, 'error') === false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    private function printUsingCUPS($zplCommands, $printer)
    {
        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'zpl_');
            file_put_contents($tempFile, implode("\n", $zplCommands));
            
            $command = "lp -d $printer $tempFile";
            $output = shell_exec($command . " 2>&1");
            
            unlink($tempFile);
            
            return strpos($output, 'request id') !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    private function printUsingNetwork($zplCommands, $printer)
    {
        // If printer has IP address (e.g., 192.168.1.100:9100)
        if (filter_var($printer, FILTER_VALIDATE_IP) || strpos($printer, ':') !== false) {
            try {
                $socket = fsockopen($printer, 9100, $errno, $errstr, 10);
                if ($socket) {
                    fwrite($socket, implode("\n", $zplCommands));
                    fclose($socket);
                    return true;
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        
        return false;
    }
    
    private function saveToFile($zplCommands)
    {
        $filename = 'qr_codes_' . date('Y-m-d_H-i-s') . '.zpl';
        $filepath = storage_path('app/print/' . $filename);
        
        // Create directory if not exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        file_put_contents($filepath, implode("\n", $zplCommands));
        
        $this->warn("Could not print directly. ZPL file saved to: $filepath");
        $this->info("You can manually send this file to your printer using:");
        $this->line("  - Windows: Right-click file -> Print");
        $this->line("  - Linux/Mac: lpr -P SATO_CG408 $filepath");
        $this->line("  - Network: Send to printer IP:9100");
    }
}
