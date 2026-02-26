<?php

namespace App\Services;

use App\Models\Aset\SubKelompok;
use Carbon\Carbon;

class SubKelompokService
{
    public static function generateId($kelompok_id, $record = null)
    {
        if (!$kelompok_id) {
            return null;
        }

        if ($record && str_starts_with($record->id, $kelompok_id)) {
            return $record->id;
        }

        $kode_registrasi = '01';
        $lastSubKelompok = SubKelompok::where('id', 'like', $kelompok_id . '.%')
            ->where('id', 'not like', '%.99')
            ->orderBy('id', 'desc')
            ->first();
        if ($lastSubKelompok) {
            $kode_registrasi = sprintf("%02d", substr($lastSubKelompok->id, -2) + 1);
        }

        return $kelompok_id . '.' . $kode_registrasi;
    }
} 