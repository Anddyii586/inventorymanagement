<?php

namespace App\Models\Aset;

use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    protected $table = 'asset_bidang';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;

    public function golongan()
    {
        return $this->belongsTo(Golongan::class, 'id_golongan');
    }
}
