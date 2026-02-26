<div class="overflow-x-auto">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">No</th>
                <th scope="col" class="px-6 py-3">Bidang</th>
                <th scope="col" class="px-6 py-3 text-right">Total Nilai (Rp)</th>
                <th scope="col" class="px-6 py-3 text-right">Total Item</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $item)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="px-6 py-4">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        {{ $item['nama_bidang'] }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        Rp {{ number_format($item['total_nilai'], 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        {{ number_format($item['total_item'], 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
