<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KoreksiPencatatan;
use Illuminate\Support\Facades\Auth;

class KoreksiPencatatanController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'asset_type' => 'required|string',
            'asset_id' => 'required|integer',
            'rows' => 'required|array',
            'rows.*.kode' => 'nullable|string',
            'rows.*.nama' => 'nullable|string',
            'rows.*.jumlah' => 'nullable|numeric',
            'rows.*.harga' => 'nullable|numeric',
            'rows.*.tercatat' => 'nullable|string',
            'rows.*.seharusnya' => 'nullable|string',
            'rows.*.keterangan' => 'nullable|string',
        ]);

        $rows = $data['rows'] ?? [];
        $totalJumlah = 0;
        $totalHarga = 0;

        // Filter out empty rows and calculate totals
        $filteredRows = [];
        foreach ($rows as $r) {
            if (!empty($r['kode']) || !empty($r['nama']) || !empty($r['jumlah']) || !empty($r['harga'])) {
                $filteredRows[] = $r;
                $totalJumlah += isset($r['jumlah']) ? (int)$r['jumlah'] : 0;
                $totalHarga += isset($r['harga']) ? (float)$r['harga'] : 0;
            }
        }

        if (empty($filteredRows)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['rows' => 'Minimal harus ada satu baris data yang diisi.']);
        }

        $koreksi = KoreksiPencatatan::create([
            'asset_type' => $data['asset_type'],
            'asset_id' => $data['asset_id'],
            'user_id' => Auth::id(),
            'data' => $filteredRows,
            'total_jumlah' => $totalJumlah,
            'total_harga' => $totalHarga,
        ]);

        return redirect()->back()->with('koreksi_success', 'Koreksi pencatatan berhasil disimpan.');
    }
}
