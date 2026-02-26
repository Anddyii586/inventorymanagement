<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Code - Thermal Printer</title>
    <style>
        @page {
            margin: 0;
            size: {{ $paper_size === '10x5' ? '100mm 50mm' : '83mm 28mm' }};
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Courier New', monospace;
            background: white;
            width: {{ $paper_size === '10x5' ? '100mm' : '83mm' }};
            height: {{ $paper_size === '10x5' ? '50mm' : '28mm' }};
        }
        
        .label-container {
            width: {{ $paper_size === '10x5' ? '100mm' : '83mm' }};
            height: {{ $paper_size === '10x5' ? '50mm' : '28mm' }};
            display: flex;
            background: white;
            page-break-inside: avoid;
        }
        
        .qr-section {
            width: {{ $paper_size === '10x5' ? '35mm' : '22mm' }};
            height: {{ $paper_size === '10x5' ? '50mm' : '28mm' }};
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: white;
        }
        
        .codes-section {
            width: {{ $paper_size === '10x5' ? '60mm' : '55mm' }};
            height: {{ $paper_size === '10x5' ? '50mm' : '28mm' }};
            display: flex;
            flex-direction: column;
            padding: {{ $paper_size === '10x5' ? '3mm' : '1mm' }};
            position: relative;
            background: white;
        }
        
        .qr-code-svg svg {
            width: 100% !important;
            height: 100% !important;
        }
        
        .qr-text {
            font-size: {{ $paper_size === '10x5' ? '8px' : '5px' }};
            color: #000;
            text-align: center;
            margin-top: {{ $paper_size === '10x5' ? '2mm' : '0.5mm' }};
            font-weight: normal;
        }
        
        .title {
            font-size: {{ $paper_size === '10x5' ? '12px' : '8px' }};
            font-weight: bold;
            color: #000;
            margin-bottom: {{ $paper_size === '10x5' ? '2mm' : '1mm' }};
            text-transform: uppercase;
        }
        
        .code-container {
            margin-bottom: {{ $paper_size === '10x5' ? '2mm' : '1mm' }};
        }
        
        .code-label {
            font-size: {{ $paper_size === '10x5' ? '8px' : '5px' }};
            color: #000;
            margin-bottom: {{ $paper_size === '10x5' ? '0.5mm' : '0.2mm' }};
            font-weight: normal;
        }
        
        .code-value {
            font-size: {{ $paper_size === '10x5' ? '12px' : '7px' }};
            font-weight: bold;
            color: #000;
            letter-spacing: {{ $paper_size === '10x5' ? '0.5px' : '0.2px' }};
            font-family: 'Courier New', monospace;
        }
        
        .print-date {
            position: absolute;
            top: {{ $paper_size === '10x5' ? '2mm' : '0.5mm' }};
            right: {{ $paper_size === '10x5' ? '2mm' : '1mm' }};
            font-size: {{ $paper_size === '10x5' ? '8px' : '5px' }};
            color: #000;
            font-weight: normal;
        }
        
        .sub-kelompok {
            position: absolute;
            bottom: {{ $paper_size === '10x5' ? '3mm' : '0.5mm' }};
            left: {{ $paper_size === '10x5' ? '3mm' : '1mm' }};
            font-size: {{ $paper_size === '10x5' ? '8px' : '5px' }};
            color: #000;
            font-weight: normal;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .label-container {
                page-break-inside: avoid;
                page-break-after: always;
            }
        }
        
        /* Thermal printer specific styles */
        .thermal-label {
            width: {{ $paper_size === '10x5' ? '100mm' : '83mm' }};
            height: {{ $paper_size === '10x5' ? '50mm' : '28mm' }};
            border: none;
            background: white;
        }
        
        /* Ensure proper spacing for thermal printing */
        .label-container:not(:last-child) {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    @foreach($data as $record)
        <div class="label-container">
            <!-- QR Code Section (Left - 22mm) -->
            <div class="qr-section">
                <div style="position: relative; width: {{ $paper_size === '10x5' ? '30mm' : '18mm' }}; height: {{ $paper_size === '10x5' ? '30mm' : '18mm' }}; background: white; display: flex; align-items: center; justify-content: center;">
                    @php
                        $qrSize = $paper_size === '10x5' ? 200 : 120;
                        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size($qrSize)
                            ->errorCorrection('H')
                            ->margin(0)
                            ->generate(route('public.peralatan-mesin.detail', $record->id));
                    @endphp
                    
                    <div class="qr-code-svg" style="width: 100%; height: 100%;">
                        {!! $qrCode !!}
                    </div>

                    @php
                        $logoPath = public_path('images/ptam.png');
                    @endphp
                    @if(file_exists($logoPath))
                        <img src="{{ asset('images/ptam.png') }}" 
                             style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 25%; height: auto; background: white; padding: 1px; border-radius: 2px;">
                    @endif
                </div>
                <div class="qr-text">untuk detail lanjut</div>
            </div>
            
            <!-- Codes Section (Right - 55mm) -->
            <div class="codes-section">
                <div class="print-date">
                    Cetak: {{ now()->format('d/m/Y H:i') }}
                </div>
                
                <div class="title">PERALATAN & MESIN</div>
                
                <div class="code-container">
                    <div class="code-value">{{ $record->kode_lokasi ?? 'XX.XX.XX.XX.XX.XX' }}</div>
                </div>
                
                <div class="code-container">
                    <div class="code-value">{{ $record->id ?? 'XX.XX.XX.XX.XX.XX.XXXX' }}</div>
                </div>
                
                <div class="sub-kelompok">
                    {{ $record->subSubKelompok->sub_sub_kelompok ?? 'Nama Sub Sub Kelompok' }}
                    @if($record->ruangan)
                        / {{ $record->ruangan->nama ?? '' }}
                    @endif
                </div>
            </div>
        </div>
    @endforeach
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>



