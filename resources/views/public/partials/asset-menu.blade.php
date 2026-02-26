<div class="bg-white rounded-lg shadow-sm p-3 mb-4">
    <nav class="flex flex-wrap gap-2 items-center">
        @php
            $items = [
                ['label' => 'Tanah', 'route' => 'public.tanah.index'],
                ['label' => 'Gedung & Bangunan', 'route' => 'public.gedung-bangunan.index'],
                ['label' => 'Peralatan & Mesin', 'route' => 'public.peralatan-mesin.index'],
                ['label' => 'Jaringan', 'route' => 'public.jaringan.index'],
                ['label' => 'Aset Tetap Lainnya', 'route' => 'public.aset-tetap-lainnya.index'],
            ];
        @endphp

        @foreach($items as $item)
            @if(Route::has($item['route']))
                <a href="{{ route($item['route']) }}"
                   class="px-3 py-1 text-sm rounded bg-gray-50 border border-gray-200 hover:bg-gray-100 text-gray-700">
                    {{ $item['label'] }}
                </a>
            @else
                <a href="#" class="px-3 py-1 text-sm rounded bg-gray-50 border border-gray-200 text-gray-400 cursor-not-allowed">
                    {{ $item['label'] }}
                </a>
            @endif
        @endforeach

        @php
            $assetType = isset($tanah) ? 'tanah' : (isset($peralatanMesin) ? 'peralatan-mesin' : (isset($gedungBangunan) ? 'gedung-bangunan' : (isset($jaringan) ? 'jaringan' : (isset($asetTetapLainnya) ? 'aset-tetap-lainnya' : ''))));
            $assetId = optional($tanah)->id ?: optional($peralatanMesin)->id ?: optional($gedungBangunan)->id ?: optional($jaringan)->id ?: optional($asetTetapLainnya)->id ?: '';
        @endphp

        <div class="ml-auto">
            <button type="button" data-asset-type="{{ $assetType }}" data-asset-id="{{ $assetId }}" class="open-koreksi-btn px-3 py-1 text-sm rounded bg-indigo-600 text-white hover:bg-indigo-700">Koreksi Pencatatan Aset</button>
        </div>
    </nav>
</div>
