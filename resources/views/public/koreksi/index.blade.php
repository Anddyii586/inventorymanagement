<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koreksi Pencatatan Aset</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    {{-- @php use Illuminate\Support\Str; @endphp
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Koreksi Pencatatan Aset</h1>
            {{-- @if(Route::has('public.tanah.index'))
                <a href="{{ route('public.tanah.index') }}" class="px-3 py-2 bg-indigo-600 text-white rounded">Daftar Aset</a>
            @else
                <a href="#" class="px-3 py-2 bg-gray-200 text-gray-600 rounded cursor-not-allowed">Daftar Aset</a>
            @endif --}}
        @include('public.partials.asset-menu')

        <div class="bg-white rounded shadow p-4">
            <p class="text-sm text-gray-600 mb-4">Daftar koreksi yang telah dibuat melalui form publik.</p>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm table-auto">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="px-3 py-2 border">ID</th>
                            <th class="px-3 py-2 border">Asset Type</th>
                            <th class="px-3 py-2 border">Asset ID</th>
                            <th class="px-3 py-2 border">Total Jumlah</th>
                            <th class="px-3 py-2 border">Total Harga</th>
                            <th class="px-3 py-2 border">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr class="odd:bg-white even:bg-gray-50">
                            <td class="px-3 py-2 border">{{ $item->id }}</td>
                            <td class="px-3 py-2 border">{{ $item->asset_type }}</td>
                            <td class="px-3 py-2 border">
                                @php
                                    $routeMap = [
                                        'tanah' => 'public.tanah.detail',
                                        'peralatan-mesin' => 'public.peralatan-mesin.detail',
                                        'gedung-bangunan' => 'public.gedung-bangunan.detail',
                                        'jaringan' => 'public.jaringan.detail',
                                        'aset-tetap-lainnya' => 'public.aset-tetap-lainnya.detail',
                                    ];
                                    $assetUrl = null;
                                    if(isset($routeMap[$item->asset_type]) && Route::has($routeMap[$item->asset_type])){
                                        try{ $assetUrl = route($routeMap[$item->asset_type], $item->asset_id); } catch (\Throwable $e) { $assetUrl = null; }
                                    }
                                @endphp
                                @if($assetUrl)
                                    <a href="{{ $assetUrl }}" class="text-indigo-600 hover:underline">#{{ $item->asset_id }}</a>
                                    @if($item->asset)
                                        <div class="text-xs text-gray-600">{{ Str::limit($item->asset->nama ?? $item->asset->name ?? '-', 60) }}</div>
                                    @endif
                                @else
                                    {{ $item->asset_id }}
                                @endif
                            </td>
                            <td class="px-3 py-2 border">{{ $item->total_jumlah }}</td>
                            <td class="px-3 py-2 border">Rp {{ number_format($item->total_harga,0,',','.') }}</td>
                            <td class="px-3 py-2 border">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="px-3 py-6 border text-center" colspan="6">Belum ada koreksi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $items->links() }}</div>
        </div>
    </div>
</body>
</html>
