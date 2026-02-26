<?php

namespace App\Models\Aset;

use Illuminate\Database\Eloquent\Model;

class SubKelompok extends Model
{
    protected $table = 'asset_sub_kelompok';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;

    public function kelompok()
    {
        return $this->belongsTo(Kelompok::class, 'id_kelompok');
    }
}
