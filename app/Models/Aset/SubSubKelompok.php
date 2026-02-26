<?php

namespace App\Models\Aset;

use Illuminate\Database\Eloquent\Model;

class SubSubKelompok extends Model
{
    protected $table = 'asset_sub_sub_kelompok';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'umur_ekonomis' => 'integer',
    ];

    public function subKelompok()
    {
        return $this->belongsTo(SubKelompok::class, 'id_sub_kelompok');
    }
}
