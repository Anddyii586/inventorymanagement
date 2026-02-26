<?php

namespace App\Models\Lokasi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ruangan extends Model
{
    use HasFactory;

    protected $table = 'ruangan';

    protected $fillable = [
        'unit_id',
        'kode',
        'nama',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
