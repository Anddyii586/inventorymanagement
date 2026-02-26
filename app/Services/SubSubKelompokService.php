<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Aset\SubSubKelompok;

class SubSubKelompokService
{
    public static function generateId($sub_kelompok_id, $record = null)
    {
        if (!$sub_kelompok_id) {
            return null;
        }

        if ($record && str_starts_with($record->id, $sub_kelompok_id)) {
            return $record->id;
        }

        $kode_registrasi = '001';
        $lastSubSubKelompok = SubSubKelompok::where('id', 'like', $sub_kelompok_id . '.%')
            ->where('id', 'not like', '%.999')
            ->orderBy('id', 'desc')
            ->first();
        if ($lastSubSubKelompok) {
            $kode_registrasi = sprintf("%03d", substr($lastSubSubKelompok->id, -3) + 1);
        }

        return $sub_kelompok_id . '.' . $kode_registrasi;
    }
} 