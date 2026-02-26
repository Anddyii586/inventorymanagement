@props([
    'record',
    'assetType' => 'TANAH/BANGUNAN',
    'routeName' => 'public.tanah.detail',
    'title' => 'TANAH/BANGUNAN :'
])

<div class="space-y-4">
    <!-- Asset Label Preview -->
    <div class="bg-white p-6 rounded-lg min-w-[900px]">
        <div class="flex items-start gap-4">
            <!-- Left side - QR Code with Logo -->
            <div class="w-[200px] flex flex-col items-center justify-center p-4 border rounded-lg">
                <div class="mb-2 relative w-[150px] h-[150px] flex items-center justify-center bg-white border rounded">
                    @php
                        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)
                            ->errorCorrection('H')
                            ->margin(0)
                            ->generate(route($routeName, $record->id));
                        
                        $logoPath = public_path('images/ptam.png');
                    @endphp
                    
                    <div class="qr-code-svg w-full h-full">
                        {!! $qrCode !!}
                    </div>

                    @if(file_exists($logoPath))
                        <img src="{{ asset('images/ptam.png') }}" 
                             style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 25%; height: auto; background: white; padding: 2px; border-radius: 4px;">
                    @endif
                </div>
                <p class="text-xs text-gray-500 text-center">Scan untuk detail aset</p>
            </div>

            <!-- Right side - Codes -->
            <div class="flex-1 border rounded-lg p-2 relative">
                <!-- Print Date - Top Right -->
                <div style="position: absolute; top: 1rem; right: 1rem; font-size: 12px; color: #6b7280;">
                    Cetak: {{ now()->format('d/m/Y H:i') }}
                </div>
                
                <div class="border px-4 pt-2 pb-6 rounded-lg" style="color: #52525b;">
                    <h2 class="font-bold mb-6" style="font-size: 32px;">{{ $title }}</h2>

                    <!-- Kode Lokasi -->
                    <div class="border-t-2 border-b-2 border-gray-400 ms-4">
                        <div class="flex flex-wrap gap-1">
                            @foreach(str_split($record->kode_lokasi ?? 'XX.XX.XX.XX.XX.XX') as $char)
                                <span class="font-bold" style="font-size: 36px;">{{ $char }}</span>
                            @endforeach
                        </div>
                    </div>
                    <hr>
                    <!-- Kode Aset -->
                    <div class="border-t-2 border-b-2 border-gray-400 ms-4">
                        <div class="flex flex-wrap gap-1">
                            @foreach(str_split($record->id ?? 'XX.XX.XX.XX.XX.XX.XXXX') as $char)
                                <span class="font-bold" style="font-size: 36px;">{{ $char }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div style="position: absolute; bottom: 1rem; left: 1.675rem; font-size: 12px; color: #6b7280;">
                    {{ $record->subSubKelompok()->value('sub_sub_kelompok') ?? 'Nama Sub Sub Kelompok' }}
                    @if($assetType === 'PERALATAN & MESIN' && $record->ruangan)
                        / {{ $record->ruangan->nama ?? '' }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-center gap-2 mt-6">
            <a href="{{ route($routeName, $record->id) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Buka Halaman
            </a>
        </div>
    </div>
</div>

<style>
    .qr-code-svg svg {
        width: 100% !important;
        height: 100% !important;
    }

    /* Custom styles for better preview */
    .preview-container {
        background: white;
        padding: 1rem;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }
</style>
