<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Massal - {{ $assetTypeName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=PT+Sans+Narrow:wght@400;700&family=PT+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "PT Sans", sans-serif;
            background: white;
            font-size: 10px;
            font-weight: 900;
            line-height: 1.2;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 5mm;
            padding: 0;
        }

        .label {
            width: 83mm;
            height: 28mm;
            display: flex;
            background: white;
            border: 0.1mm solid #eee;
            page-break-inside: avoid;
            padding: 2mm;
            box-sizing: border-box;
        }

        .qr-section {
            width: 22mm;
            height: 24mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .codes-section {
            flex: 1;
            height: 24mm;
            display: flex;
            flex-direction: column;
            position: relative;
            background: white;
            margin-left: 4mm;
            justify-content: center;
        }

        .qr-code-svg svg {
            width: 100% !important;
            height: 100% !important;
        }

        .qr-text {
            text-align: center;
            font-size: 7px;
            margin-top: 1mm;
        }

        .title {
            font-size: 11px;
            margin-bottom: 2mm;
            text-transform: uppercase;
            border-bottom: 0.3mm solid #333;
            padding-bottom: 0.5mm;
        }

        .code-container {
            margin-bottom: 0.5mm;
        }

        .code-label {
            font-size: 8px;
            color: #666;
        }

        .code-value {
            font-size: 10px;
            letter-spacing: 0.2px;
            font-family: "PT Sans", sans-serif;
            font-weight: bold;
        }

        .sub-kelompok {
            font-size: 8px;
            margin-top: 1.5mm;
            font-style: italic;
            color: #444;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .label {
                border: 0.1mm solid #ccc;
            }

            .d-print-none {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="d-print-none" style="background: #f4f4f4; padding: 10px; text-align: center; border-bottom: 1px solid #ddd;">
        <button onclick="window.print()" style="padding: 8px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
            CETAK SEKARANG
        </button>
        <p style="margin-top: 5px; font-weight: normal; color: #666;">Gunakan layout Landscape atau Portrait sesuai kebutuhan penggaris label Anda.</p>
    </div>

    <div class="grid-container">
        @foreach($data as $record)
            <div class="label">
                <!-- QR Code Section -->
                <div class="qr-section">
                    <div style="position: relative; width: 18mm; height: 18mm; background: white; display: flex; align-items: center; justify-content: center;">
                        @php
                            $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)
                                ->errorCorrection('H')
                                ->margin(0)
                                ->generate(route($routeName, $record->id));
                        @endphp
                        
                        <div class="qr-code-svg" style="width: 100%; height: 100%;">
                            {!! $qrCode !!}
                        </div>

                        @php
                            $logoPath = public_path('images/ptam_bw.png');
                        @endphp
                        @if(file_exists($logoPath))
                            <img src="{{ asset('images/ptam_bw.png') }}" 
                                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 25%; height: auto; background: white; padding: 1px; border-radius: 2px;">
                        @endif
                    </div>
                    <div class="qr-text">Scan detail aset</div>
                </div>

                <!-- Codes Section -->
                <div class="codes-section">
                    <div class="title">{{ $assetTypeName }}</div>

                    <div class="code-container">
                        <div class="code-label">Kode Lokasi:</div>
                        <div class="code-value">{{ $record->kode_lokasi ?? 'XX.XX.XX.XX.XX.XX' }}</div>
                    </div>

                    <div class="code-container">
                        <div class="code-label">Kode Aset:</div>
                        <div class="code-value">{{ $record->id ?? 'XX.XX.XX.XX.XX.XX.XXXX' }}</div>
                    </div>

                    <div class="sub-kelompok">
                        {{ $record->subSubKelompok?->sub_sub_kelompok ?? 'N/A' }}
                        @if(isset($record->ruangan))
                            | {{ $record->ruangan->nama ?? '' }}
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        // Optional: Auto print
        // window.onload = function () { window.print(); };
    </script>
</body>

</html>
