<div class="mt-2">
    @php
        $assetType = isset($tanah) ? 'tanah' : (isset($peralatanMesin) ? 'peralatan-mesin' : (isset($gedungBangunan) ? 'gedung-bangunan' : (isset($jaringan) ? 'jaringan' : (isset($asetTetapLainnya) ? 'aset-tetap-lainnya' : ''))));
        $assetId = optional($tanah)->id ?: optional($peralatanMesin)->id ?: optional($gedungBangunan)->id ?: optional($jaringan)->id ?: optional($asetTetapLainnya)->id ?: '';
    @endphp

    <button type="button" data-asset-type="{{ $assetType }}" data-asset-id="{{ $assetId }}" class="open-koreksi-btn inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700">Koreksi Pencatatan Aset</button>
</div>
