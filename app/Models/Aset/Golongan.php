<?php

namespace App\Models\Aset;

use Illuminate\Database\Eloquent\Model;

class Golongan extends Model
{
    protected $table = 'asset_golongan';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = false;
}
