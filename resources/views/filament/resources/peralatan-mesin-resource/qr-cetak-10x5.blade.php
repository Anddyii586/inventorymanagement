<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Code - Peralatan & Mesin (10x5cm)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=PT+Sans+Narrow:wght@400;700&family=PT+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "PT Sans", sans-serif;
            background: white;
            font-size: 12px;
            font-weight: 900;
        }

        .print-container {
            width: 100%;
        }

        .label-row {
            display: flex;
            page-break-inside: avoid;
        }

        .label {
            width: 100mm;
            height: 50mm;
            display: flex;
            background: white;
            page-break-inside: avoid;
            border: 1px solid #333;
        }

        .qr-section {
            width: 35mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .codes-section {
            width: 60mm;
            display: flex;
            flex-direction: column;
            position: relative;
            background: white;
            padding: 3mm;
        }

        .qr-code-svg svg {
            width: 100% !important;
            height: 100% !important;
        }

        .qr-text {
            text-align: center;
            margin-top: 2mm;
        }

        .title {
            font-size: 16px;
            margin-top: 3mm;
            text-transform: uppercase;
        }

        .code-container {
            margin-bottom: 0.5mm;
        }

        .code-label {
        }

        .code-value {
            font-size: 22px;
            letter-spacing: 0.5px;
            font-family: "PT Sans Narrow", sans-serif;
            /* font-family: 'Courier New', monospace; */
        }

        .print-date {
            position: absolute;
            top: 1mm;
            right: 2mm;
        }

        .sub-kelompok {
            position: absolute;
            bottom: 3mm;
            max-width: 55mm;
            word-wrap: break-word;
        }

        .room-info {
            position: absolute;
            bottom: 8mm;
            max-width: 55mm;
            word-wrap: break-word;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                zoom: 100%;
            }

            .label-row {
                page-break-inside: avoid;
                zoom: 1.0;
            }

            .label {
                page-break-inside: avoid;
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>

<body>
    <div class="print-container">
        @foreach($data as $record)
            <div class="label-row">
                <div class="label">
                    <!-- QR Code Section (Left - Square) -->
                    <div class="qr-section">
                        <div style="position: relative; width: {{ $paper_size === '10x5' ? '30mm' : '18mm' }}; height: {{ $paper_size === '10x5' ? '30mm' : '18mm' }}; background: white; display: flex; align-items: center; justify-content: center;">
                            @php
                                $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)
                                    ->errorCorrection('H')
                                    ->margin(0)
                                    ->generate($record->id);
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
                        <div class="qr-text">Scan untuk detail aset</div>
                        <div class="code-label" style="margin-top: 1mm; margin-bottom: -3.2mm; font-size: 10px;">QR-AST.PA/01-03</div>
                    </div>

                    <!-- Codes Section (Right - Rectangle) -->
                    <div class="codes-section">
                        {{-- <div class="print-date">
                            Cetak: {{ now()->format('d/m/Y H:i') }}
                        </div> --}}

                        <div class="title">PERALATAN & MESIN :</div>

                        <div class="code-container">
                            <div class="code-label">Kode Lokasi:</div>
                            <div class="code-value">{{ $record->kode_lokasi ?? 'XX.XX.XX.XX.XX.XX' }}</div>
                        </div>

                        <div class="code-container">
                            <div class="code-label">Kode Aset:</div>
                            <div class="code-value">{{ $record->id ?? 'XX.XX.XX.XX.XX.XX.XXXX' }}</div>
                        </div>

                        <div class="code-container">
                            <div class="code-label">{{ $record->subSubKelompok()->first()->sub_sub_kelompok ?? 'Nama Sub Sub Kelompok' }}</div>
                            @if($record->ruangan)
                                <div class="code-label">Ruangan: {{ $record->ruangan->nama ?? '' }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        // Auto print when page loads
        window.onload = function () {
            window.print();
        };
    </script>
</body>

</html>
