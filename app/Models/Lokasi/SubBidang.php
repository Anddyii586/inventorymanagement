<?php

namespace App\Models\Lokasi;

use Illuminate\Database\Eloquent\Model;

class SubBidang extends Model
{
    protected $table = 'struktur_sub_bidang';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;


    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'id_bidang');
    }
}
