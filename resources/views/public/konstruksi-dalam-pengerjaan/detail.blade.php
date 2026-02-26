<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Konstruksi Dalam Pengerjaan - {{ $konstruksiDalamPengerjaan->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 mb-4 sm:mb-6">
                <div class="flex flex-col sm:flex-row items-center sm:items-start justify-between space-y-4 sm:space-y-0">
                    <div class="flex flex-col sm:flex-row items-center sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo AMGM" class="h-12 sm:h-16">
                        <div class="text-center sm:text-left">
                            <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800">Detail Konstruksi Dalam Pengerjaan</h1>
                            <p class="text-sm sm:text-base text-gray-600">Sistem Informasi Aset Tetap</p>
                        </div>
                    </div>
                    <div class="text-center sm:text-right">
                        <div class="text-xs sm:text-sm text-gray-500">Kode Aset</div>
                        <div class="text-lg sm:text-xl font-bold text-gray-800">{{ $konstruksiDalamPengerjaan->id }}</div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">
                <!-- Asset Information -->
                <div class="xl:col-span-2 space-y-4 sm:space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-3 sm:mb-4">Informasi Dasar</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-600">Nama Barang</label>
                                <p class="mt-1 text-sm sm:text-base text-gray-800">{{ $konstruksiDalamPengerjaan->nama_barang ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-600">Kode Barang</label>
                                <p class="mt-1 text-sm sm:text-base text-gray-800">{{ $konstruksiDalamPengerjaan->kode_barang ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-600">Register</label>
                                <p class="mt-1 text-sm sm:text-base text-gray-800">{{ $konstruksiDalamPengerjaan->register ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-600">Kondisi</label>
                                <p class="mt-1 text-sm sm:text-base text-gray-800">{{ $konstruksiDalamPengerjaan->kondisi ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Location Information -->
                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-3 sm:mb-4">Informasi Lokasi</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-600">Kode Lokasi</label>
                                <p class="mt-1 text-sm sm:text-base text-gray-800 font-mono">{{ $konstruksiDalamPengerjaan->kode_lokasi ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-600">Wilayah</label>
                                <p class="mt-1 text-sm sm:text-base text-gray-800">{{ $konstruksiDalamPengerjaan->wilayah->wilayah ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-600">Bidang</label>
                                <p class="mt-1 text-sm sm:text-base text-gray-800">{{ $konstruksiDalamPengerjaan->subBidang->bidang->bidang ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-600">Sub Bidang</label>
                                <p class="mt-1 text-sm sm:text-base text-gray-800">{{ $konstruksiDalamPengerjaan->subBidang->sub_bidang ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-600">Unit</label>
                                <p class="mt-1 text-sm sm:text-base text-gray-800">{{ $konstruksiDalamPengerjaan->unit->unit ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-600">Tahun Penempatan</label>
                                <p class="mt-1 text-sm sm:text-base text-gray-800">{{ $konstruksiDalamPengerjaan->tahun ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Asset Classification -->
                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-3 sm:mb-4">Klasifikasi Aset</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-600">Sub Sub Kelompok</label>
                                <p class="mt-1 text-sm sm:text-base text-gray-800">{{ $konstruksiDalamPengerjaan->subSubKelompok->sub_sub_kelompok ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-600">Tanggal Pengadaan</label>
                                <p class="mt-1 text-sm sm:text-base text-gray-800">{{ $konstruksiDalamPengerjaan->tanggal_pengadaan ? \Carbon\Carbon::parse($konstruksiDalamPengerjaan->tanggal_pengadaan)->format('d/m/Y') : '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-600">Asal Usul</label>
                                <p class="mt-1 text-sm sm:text-base text-gray-800">{{ $konstruksiDalamPengerjaan->asal_usul ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-600">Harga</label>
                                <p class="mt-1 text-sm sm:text-base text-gray-800">{{ $konstruksiDalamPengerjaan->harga ? 'Rp ' . number_format($konstruksiDalamPengerjaan->harga, 0, ',', '.') : '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-3 sm:mb-4">Informasi Tambahan</h2>
                        <div class="space-y-3 sm:space-y-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-600">Keterangan</label>
                                <p class="mt-1 text-sm sm:text-base text-gray-800">{{ $konstruksiDalamPengerjaan->keterangan ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-600">Dokumentasi</label>
                                <p class="mt-1 text-sm sm:text-base text-gray-800">{{ $konstruksiDalamPengerjaan->dokumentasi ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-4 sm:space-y-6">
                    <!-- QR Code -->
                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">QR Code</h3>
                        <div class="text-center">
                            <div class="flex justify-center">
                                {!! QrCode::size(150)->generate(url()->current()) !!}
                            </div>
                            <p class="text-xs sm:text-sm text-gray-600 mt-2">Scan untuk detail aset</p>
                        </div>
                    </div>

                    <!-- Asset Images -->
                    @if(isset($images) && count($images) > 0)
                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Foto Aset ({{ count($images) }} foto)</h3>
                        <div class="space-y-2 sm:space-y-3">
                            @foreach($images as $index => $imageUrl)
                                <div class="border rounded-lg overflow-hidden">
                                    <img src="{{ $imageUrl }}" 
                                         alt="Foto Aset {{ $index + 1 }}" 
                                         class="w-full h-full object-cover">
                                    <div class="p-2 bg-gray-50 text-center text-xs sm:text-sm text-gray-600">
                                        Foto {{ $index + 1 }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Documents -->
                    @if(isset($documents) && count($documents) > 0)
                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Dokumen ({{ count($documents) }} file)</h3>
                        <div class="space-y-2">
                            @foreach($documents as $document)
                                <div class="flex items-center justify-between p-2 sm:p-3 border rounded">
                                    <span class="text-xs sm:text-sm text-gray-700 truncate pr-2">{{ $document['name'] }}</span>
                                    <a href="{{ $document['url'] }}" target="_blank" 
                                       class="text-blue-600 hover:text-blue-800 text-xs sm:text-sm whitespace-nowrap">
                                        Download
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- System Information -->
                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Informasi Sistem</h3>
                        <div class="space-y-2 text-xs sm:text-sm">
                            <div>
                                <span class="text-gray-600">Dibuat pada:</span>
                                <span class="text-gray-800 block sm:inline">{{ $konstruksiDalamPengerjaan->created_at ? \Carbon\Carbon::parse($konstruksiDalamPengerjaan->created_at)->format('d/m/Y H:i') : '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Diubah pada:</span>
                                <span class="text-gray-800 block sm:inline">{{ $konstruksiDalamPengerjaan->updated_at ? \Carbon\Carbon::parse($konstruksiDalamPengerjaan->updated_at)->format('d/m/Y H:i') : '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">User:</span>
                                <span class="text-gray-800 block sm:inline">{{ $konstruksiDalamPengerjaan->user ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 