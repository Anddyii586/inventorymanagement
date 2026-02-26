<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KoreksiPencatatan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;

class KoreksiController extends Controller
{
    public function index()
    {
        // If the migration hasn't been run yet the table won't exist — avoid hard exception.
        if (! Schema::hasTable('koreksi_pencatatan')) {
            $items = new LengthAwarePaginator([], 0, 25, 1, [
                'path' => request()->url(),
            ]);
            return view('public.koreksi.index', compact('items'));
        }

        $items = KoreksiPencatatan::latest()->paginate(25);
        return view('public.koreksi.index', compact('items'));
    }
}
