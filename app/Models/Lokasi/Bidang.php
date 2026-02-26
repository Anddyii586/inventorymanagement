<?php

namespace App\Models\Lokasi;

use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    protected $table = 'struktur_bidang';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;

    public function direktorat()
    {
        return $this->belongsTo(Direktorat::class, 'id_direktorat');
    }

    public function subBidang()
    {
        return $this->hasMany(SubBidang::class, 'id_bidang');
    }
}
