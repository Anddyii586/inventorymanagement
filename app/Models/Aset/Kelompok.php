<?php

namespace App\Models\Aset;

use Illuminate\Database\Eloquent\Model;

class Kelompok extends Model
{
    protected $table = 'asset_kelompok';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;

    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'id_bidang');
    }
}
