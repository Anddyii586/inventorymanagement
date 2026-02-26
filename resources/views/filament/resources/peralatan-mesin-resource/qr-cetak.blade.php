<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Code - Peralatan & Mesin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=PT+Sans+Narrow:wght@400;700&family=PT+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "PT Sans", sans-serif;
            background: white;
            font-size: 10px;
            font-weight: 900;
            line-height: 1.2;
        }

        .print-container {
            width: 100%;
            max-width: 83mm;
            border: 1px solid #333;
            padding: 0mm 2mm 2mm 2mm;
        }

        .label-row {
            display: flex;
            page-break-inside: avoid;
        }

        .label {
            width: 83mm;
            height: 28mm;
            display: flex;
            background: white;
            page-break-inside: avoid;
            margin: 0 3mm;
        }

        .qr-section {
            width: 22mm;
            height: 28mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .codes-section {
            width: 54mm;
            height: 28mm;
            display: flex;
            flex-direction: column;
            position: relative;
            background: white;
            margin-left: 6mm;
        }

        .qr-code-svg svg {
            width: 100% !important;
            height: 100% !important;
        }

        .qr-text {
            text-align: center;
            font-size: 7.5px;
        }

        .title {
            font-size: 12px;
            /* margin-top: 4mm; */
            margin-top: 2mm;
            text-transform: uppercase;
        }

        .code-container {
            margin-bottom: 0.5mm;
        }

        .code-label {
        }

        .code-value {
            font-size: 11px;
            letter-spacing: 0.3px;
            font-family: "PT Sans", sans-serif;
        }

        .print-date {
            position: absolute;
            top: 1mm;
            right: 2mm;
            font-size: 9px;
        }

        .sub-kelompok {
            position: absolute;
            max-width: 55mm;
            word-wrap: break-word;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                zoom: 57%;
            }

            .label-row {
                page-break-inside: avoid;
                zoom: 1.5;
            }

            .label {
                page-break-inside: avoid;
                box-shadow: none;
            }

            .print-container {
                border: none;
            }

            .d-print-none {
                display: none;
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
                        <div style="position: relative; width: 18mm; height: 18mm; background: white; display: flex; align-items: center; justify-content: center;">
                            @php
                                $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)
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
                        <div class="code-label" style="font-size: 8px;">QR-AST.PA/01-03</div>
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
                            <div class="code-label">{{ $record->subSubKelompok()->first()->sub_sub_kelompok ?? 'Nama Sub Sub Kelompok' }}
                                @if($record->ruangan)
                                    | Ruangan: {{ $record->ruangan->nama ?? '' }}
                                @endif
                            </div>
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