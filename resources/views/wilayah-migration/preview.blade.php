<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Migrasi Kode Wilayah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">Preview Migrasi Kode Wilayah</h1>
                
                <!-- Perubahan Kode Wilayah -->
                @if(!empty($preview['wilayah_changes']))
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Perubahan Kode Wilayah</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 border-b text-left">Kode Lama</th>
                                    <th class="px-4 py-2 border-b text-left">Kode Baru</th>
                                    <th class="px-4 py-2 border-b text-left">Nama Wilayah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($preview['wilayah_changes'] as $change)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b">{{ $change['old_code'] }}</td>
                                    <td class="px-4 py-2 border-b">{{ $change['new_code'] }}</td>
                                    <td class="px-4 py-2 border-b">{{ $change['wilayah'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                <div class="mb-8">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <p class="text-yellow-800">Tidak ada perubahan kode wilayah yang akan dilakukan.</p>
                    </div>
                </div>
                @endif
                
                <!-- Perubahan Peralatan Mesin -->
                @if(!empty($preview['peralatan_mesin_changes']))
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">
                        Perubahan Kode Lokasi Peralatan Mesin 
                        <span class="text-sm font-normal text-gray-500">({{ count($preview['peralatan_mesin_changes']) }} item)</span>
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 border-b text-left">ID Aset</th>
                                    <th class="px-4 py-2 border-b text-left">Kode Lokasi Lama</th>
                                    <th class="px-4 py-2 border-b text-left">Kode Lokasi Baru</th>
                                    <th class="px-4 py-2 border-b text-left">Wilayah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($preview['peralatan_mesin_changes'], 0, 20) as $change)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b font-mono text-sm">{{ $change['id'] }}</td>
                                    <td class="px-4 py-2 border-b font-mono text-sm">{{ $change['old_kode_lokasi'] }}</td>
                                    <td class="px-4 py-2 border-b font-mono text-sm">{{ $change['new_kode_lokasi'] }}</td>
                                    <td class="px-4 py-2 border-b">{{ $change['wilayah'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if(count($preview['peralatan_mesin_changes']) > 20)
                        <p class="text-sm text-gray-600 mt-2">Menampilkan 20 dari {{ count($preview['peralatan_mesin_changes']) }} perubahan.</p>
                        @endif
                    </div>
                </div>
                @else
                <div class="mb-8">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <p class="text-yellow-800">Tidak ada perubahan kode lokasi peralatan mesin yang akan dilakukan.</p>
                    </div>
                </div>
                @endif
                
                <!-- Perubahan Tanah -->
                @if(!empty($preview['tanah_changes']))
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">
                        Perubahan Kode Lokasi Tanah 
                        <span class="text-sm font-normal text-gray-500">({{ count($preview['tanah_changes']) }} item)</span>
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 border-b text-left">ID Aset</th>
                                    <th class="px-4 py-2 border-b text-left">Kode Lokasi Lama</th>
                                    <th class="px-4 py-2 border-b text-left">Kode Lokasi Baru</th>
                                    <th class="px-4 py-2 border-b text-left">Wilayah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($preview['tanah_changes'], 0, 20) as $change)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b font-mono text-sm">{{ $change['id'] }}</td>
                                    <td class="px-4 py-2 border-b font-mono text-sm">{{ $change['old_kode_lokasi'] }}</td>
                                    <td class="px-4 py-2 border-b font-mono text-sm">{{ $change['new_kode_lokasi'] }}</td>
                                    <td class="px-4 py-2 border-b">{{ $change['wilayah'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if(count($preview['tanah_changes']) > 20)
                        <p class="text-sm text-gray-600 mt-2">Menampilkan 20 dari {{ count($preview['tanah_changes']) }} perubahan.</p>
                        @endif
                    </div>
                </div>
                @else
                <div class="mb-8">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <p class="text-yellow-800">Tidak ada perubahan kode lokasi tanah yang akan dilakukan.</p>
                    </div>
                </div>
                @endif
                
                <!-- Tombol Aksi -->
                <div class="flex justify-between items-center pt-6 border-t">
                    <div class="text-sm text-gray-600">
                        <p>Total perubahan yang akan dilakukan:</p>
                        <ul class="list-disc list-inside mt-1">
                            <li>Wilayah: {{ count($preview['wilayah_changes']) }}</li>
                            <li>Peralatan Mesin: {{ count($preview['peralatan_mesin_changes']) }}</li>
                            <li>Tanah: {{ count($preview['tanah_changes']) }}</li>
                        </ul>
                    </div>
                    
                    <div class="flex space-x-4">
                        <a href="{{ url('/') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                            Kembali
                        </a>
                        <form action="{{ route('wilayah.migrate') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600" 
                                    onclick="return confirm('Apakah Anda yakin ingin melakukan migrasi? Ini akan mengubah data di database.')">
                                Jalankan Migrasi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 