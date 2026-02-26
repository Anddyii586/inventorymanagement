<x-filament-panels::page>

    <div class="mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-medium tracking-tight">Koreksi Pencatatan Aset</h1>
            <div class="flex items-center gap-2">
                <x-filament::button type="button" color="gray" onclick="document.getElementById('koreksiForm').reset();">Reset</x-filament::button>
                <x-filament::button form="koreksiForm" type="submit" color="primary">Simpan Koreksi</x-filament::button>
            </div>
        </div>
    </div>

    <x-filament::card>
        <form method="POST" action="{{ route('public.koreksi.store') }}" id="koreksiForm">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm text-gray-600">Asset Type</label>
                    <select name="asset_type" id="asset_type" class="mt-1 block w-full rounded-md border-gray-200 text-sm shadow-sm focus:ring-1 focus:ring-primary-600">
                        <option value="">Pilih tipe aset (opsional)</option>
                        <option value="tanah">Tanah</option>
                        <option value="gedung-bangunan">Gedung & Bangunan</option>
                        <option value="peralatan-mesin">Peralatan & Mesin</option>
                        <option value="jaringan">Jaringan</option>
                        <option value="aset-tetap-lainnya">Aset Tetap Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Asset ID</label>
                    <input type="number" name="asset_id" id="asset_id" class="mt-1 block w-full rounded-md border-gray-200 text-sm shadow-sm" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm table-auto">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="px-2 py-2 border text-center">No</th>
                            <th class="px-2 py-2 border">Kode Barang</th>
                            <th class="px-2 py-2 border">Nama/Jenis Barang</th>
                            <th class="px-2 py-2 border text-center">Jumlah (Barang)</th>
                            <th class="px-2 py-2 border text-right">Jumlah (Harga)</th>
                            <th class="px-2 py-2 border">Tercatat di KIB</th>
                            <th class="px-2 py-2 border">Seharusnya</th>
                            <th class="px-2 py-2 border">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 1; $i <= 7; $i++)
                        <tr class="bg-white">
                            <td class="border px-2 py-2 text-center align-top">{{ $i }}</td>
                            <td class="border px-2 py-2"><input type="text" name="rows[{{ $i }}][kode]" class="w-full text-sm p-2 rounded-md border-gray-200 shadow-sm"/></td>
                            <td class="border px-2 py-2"><input type="text" name="rows[{{ $i }}][nama]" class="w-full text-sm p-2 rounded-md border-gray-200 shadow-sm"/></td>
                            <td class="border px-2 py-2 text-center"><input type="number" min="0" step="1" value="0" name="rows[{{ $i }}][jumlah]" data-row="{{ $i }}" class="w-20 text-sm p-2 rounded-md border-gray-200 shadow-sm jumlah-field text-center"/></td>
                            <td class="border px-2 py-2 text-right"><input type="number" min="0" step="0.01" value="0" name="rows[{{ $i }}][harga]" data-row="{{ $i }}" class="w-36 text-sm p-2 rounded-md border-gray-200 shadow-sm harga-field text-right"/></td>
                            <td class="border px-2 py-2"><textarea name="rows[{{ $i }}][tercatat]" rows="2" class="w-full text-sm p-2 rounded-md border-gray-200 shadow-sm"></textarea></td>
                            <td class="border px-2 py-2"><textarea name="rows[{{ $i }}][seharusnya]" rows="2" class="w-full text-sm p-2 rounded-md border-gray-200 shadow-sm"></textarea></td>
                            <td class="border px-2 py-2"><input type="text" name="rows[{{ $i }}][keterangan]" class="w-full text-sm p-2 rounded-md border-gray-200 shadow-sm"/></td>
                        </tr>
                        @endfor
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 font-semibold">
                            <td class="border px-2 py-2 text-center">&nbsp;</td>
                            <td class="border px-2 py-2 text-center" colspan="1">JUMLAH</td>
                            <td class="border px-2 py-2"></td>
                            <td class="border px-2 py-2 text-center"><input type="text" id="totalJumlah" readonly class="w-20 text-sm p-2 bg-gray-50 border rounded-md text-center"/></td>
                            <td class="border px-2 py-2 text-right"><input type="text" id="totalHarga" readonly class="w-36 text-sm p-2 bg-gray-50 border rounded-md text-right"/></td>
                            <td class="border px-2 py-2" colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </form>
    </x-filament::card>

    <script>
        (function(){
            function formatCurrency(num){
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
            }
            function parseNumber(value){ var n = parseFloat(value); return isNaN(n) ? 0 : n; }

            function calculateTotalsWithin(root){
                const jumlahFields = root.querySelectorAll('.jumlah-field');
                const hargaFields = root.querySelectorAll('.harga-field');
                let totalJumlah = 0; let totalHarga = 0;
                jumlahFields.forEach(f => { totalJumlah += parseNumber(f.value); });
                hargaFields.forEach(f => { totalHarga += parseNumber(f.value); });
                const totalJumlahEl = document.getElementById('totalJumlah');
                const totalHargaEl = document.getElementById('totalHarga');
                if(totalJumlahEl) totalJumlahEl.value = totalJumlah;
                if(totalHargaEl) totalHargaEl.value = formatCurrency(totalHarga);
            }

            document.addEventListener('DOMContentLoaded', function(){
                const form = document.getElementById('koreksiForm');
                if(form){
                    form.addEventListener('input', function(e){ if(e.target.classList && (e.target.classList.contains('jumlah-field') || e.target.classList.contains('harga-field'))) calculateTotalsWithin(form); });
                    calculateTotalsWithin(form);
                }
            });
        })();
    </script>

</x-filament-panels::page>
