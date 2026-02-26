<div id="koreksiModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white w-full max-w-4xl rounded-lg shadow-lg overflow-auto max-h-[90vh]">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-semibold">Koreksi Pencatatan</h3>
            <button id="closeKoreksiBtn" class="text-gray-600 hover:text-gray-800">Tutup ✕</button>
        </div>

        <div class="p-4">
            <form method="POST" action="{{ route('public.koreksi.store') }}" id="koreksiModalForm">
                @csrf
                <input type="hidden" name="asset_type" id="modal_asset_type" value="" />
                <input type="hidden" name="asset_id" id="modal_asset_id" value="" />

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700">
                                <th class="border px-2 py-2 text-center">No</th>
                                <th class="border px-2 py-2">Kode Barang</th>
                                <th class="border px-2 py-2">Nama/Jenis Barang</th>
                                <th class="border px-2 py-2 text-center">Jumlah (Barang)</th>
                                <th class="border px-2 py-2 text-right">Jumlah (Harga)</th>
                                <th class="border px-2 py-2">Tercatat di KIB</th>
                                <th class="border px-2 py-2">Seharusnya</th>
                                <th class="border px-2 py-2">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i = 1; $i <= 7; $i++)
                            <tr class="bg-white">
                                <td class="border px-2 py-2 text-center align-top">{{ $i }}</td>
                                <td class="border px-2 py-2"><input type="text" name="rows[{{ $i }}][kode]" class="w-full text-xs p-1 border rounded"/></td>
                                <td class="border px-2 py-2"><input type="text" name="rows[{{ $i }}][nama]" class="w-full text-xs p-1 border rounded"/></td>
                                <td class="border px-2 py-2"><input type="number" min="0" step="1" value="0" name="rows[{{ $i }}][jumlah]" data-row="{{ $i }}" class="w-20 text-xs p-1 border rounded jumlah-field text-center"/></td>
                                <td class="border px-2 py-2 text-right"><input type="number" min="0" step="0.01" value="0" name="rows[{{ $i }}][harga]" data-row="{{ $i }}" class="w-36 text-xs p-1 border rounded harga-field text-right"/></td>
                                <td class="border px-2 py-2"><textarea name="rows[{{ $i }}][tercatat]" rows="2" class="w-full text-xs p-1 border rounded"></textarea></td>
                                <td class="border px-2 py-2"><textarea name="rows[{{ $i }}][seharusnya]" rows="2" class="w-full text-xs p-1 border rounded"></textarea></td>
                                <td class="border px-2 py-2"><input type="text" name="rows[{{ $i }}][keterangan]" class="w-full text-xs p-1 border rounded"/></td>
                            </tr>
                            @endfor
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 font-semibold">
                                <td class="border px-2 py-2 text-center">&nbsp;</td>
                                <td class="border px-2 py-2 text-center" colspan="1">JUMLAH</td>
                                <td class="border px-2 py-2"></td>
                                <td class="border px-2 py-2 text-center"><input type="text" id="modal_totalJumlah" readonly class="w-20 text-xs p-1 bg-gray-100 border rounded text-center"/></td>
                                <td class="border px-2 py-2 text-right"><input type="text" id="modal_totalHarga" readonly class="w-36 text-xs p-1 bg-gray-100 border rounded text-right"/></td>
                                <td class="border px-2 py-2" colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-3 flex items-center space-x-2">
                    <button type="button" id="modalCalculateBtn" class="px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded">Hitung Jumlah</button>
                    <button type="submit" class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded">Simpan Koreksi</button>
                    <button type="button" id="modalResetBtn" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded">Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
            const totalJumlahEl = root.querySelector('#modal_totalJumlah');
            const totalHargaEl = root.querySelector('#modal_totalHarga');
            if(totalJumlahEl) totalJumlahEl.value = totalJumlah;
            if(totalHargaEl) totalHargaEl.value = formatCurrency(totalHarga);
        }

        document.addEventListener('DOMContentLoaded', function(){
            const openBtns = document.querySelectorAll('.open-koreksi-btn');
            const modal = document.getElementById('koreksiModal');
            const closeBtn = document.getElementById('closeKoreksiBtn');
            const modalForm = document.getElementById('koreksiModalForm');

            if(openBtns && modal){
                openBtns.forEach(function(openBtn){
                    openBtn.addEventListener('click', function(){
                        const type = this.dataset.assetType || '';
                        const id = this.dataset.assetId || '';
                        document.getElementById('modal_asset_type').value = type;
                        document.getElementById('modal_asset_id').value = id;
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    });
                });
            }

            if(closeBtn && modal){ closeBtn.addEventListener('click', function(){ modal.classList.add('hidden'); modal.classList.remove('flex'); }); }

            // Calculate, reset inside modal
            const calcBtn = document.getElementById('modalCalculateBtn');
            const resetBtn = document.getElementById('modalResetBtn');
            if(calcBtn){ calcBtn.addEventListener('click', function(){ calculateTotalsWithin(modal); }); }
            if(resetBtn){ resetBtn.addEventListener('click', function(){
                modal.querySelectorAll('input[type="text"], input[type="number"], textarea').forEach(i => {
                    if(i.classList.contains('jumlah-field') || i.classList.contains('harga-field')) i.value = 0; else i.value = '';
                });
                calculateTotalsWithin(modal);
            }); }

            // live recalc
            modal.addEventListener('input', function(e){ if(e.target.classList && (e.target.classList.contains('jumlah-field') || e.target.classList.contains('harga-field'))) calculateTotalsWithin(modal); });
        });
    })();
</script>
