<x-filament-panels::page>
    <x-filament-panels::form wire:submit="create">
        {{ $this->form }}

        <div class="flex items-center justify-between gap-3 pt-6">
            <div></div>
            <div class="flex items-center gap-3">
                <x-filament::button
                    type="button"
                    color="gray"
                    wire:click="reset"
                >
                    Reset
                </x-filament::button>

                <x-filament::button
                    type="submit"
                    color="primary"
                >
                    Simpan Koreksi
                </x-filament::button>
            </div>
        </div>
    </x-filament-panels::form>

    @if(!empty($koreksiList))
    <div class="mt-12">
        <h2 class="text-xl font-bold mb-4">Daftar Koreksi yang Telah Disimpan</h2>
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Tipe Aset</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Asset ID</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Total Jumlah</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Total Harga</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($koreksiList as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-6 py-3 text-gray-900 dark:text-gray-100">
                            @if($item['asset_type'] === 'tanah')
                                Tanah
                            @elseif($item['asset_type'] === 'gedung-bangunan')
                                Gedung & Bangunan
                            @elseif($item['asset_type'] === 'peralatan-mesin')
                                Peralatan & Mesin
                            @elseif($item['asset_type'] === 'jaringan')
                                Jaringan
                            @elseif($item['asset_type'] === 'aset-tetap-lainnya')
                                Aset Tetap Lainnya
                            @else
                                {{ $item['asset_type'] }}
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-900 dark:text-gray-100">{{ $item['asset_id'] }}</td>
                        <td class="px-6 py-3 text-gray-900 dark:text-gray-100">{{ $item['total_jumlah'] ?? 0 }}</td>
                        <td class="px-6 py-3 text-gray-900 dark:text-gray-100">Rp {{ number_format($item['total_harga'] ?? 0, 0, ',', '.') }}</td>
                        <td class="px-6 py-3 text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($item['created_at'])->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-filament-panels::page>
