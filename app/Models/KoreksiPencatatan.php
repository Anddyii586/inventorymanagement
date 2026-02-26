<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KoreksiPencatatan extends Model
{
    use HasFactory;

    protected $table = 'koreksi_pencatatan';

    protected $fillable = [
        'asset_type',
        'asset_id',
        'user_id',
        'data',
        'total_jumlah',
        'total_harga',
        'keterangan',
    ];

    protected $casts = [
        'data' => 'array',
        'total_harga' => 'float',
        'total_jumlah' => 'integer',
    ];

    public function asset()
    {
        $map = [
            'tanah' => \App\Models\Tanah::class,
            'peralatan-mesin' => \App\Models\PeralatanMesin::class,
            'gedung-bangunan' => \App\Models\GedungBangunan::class,
            'jaringan' => \App\Models\Jaringan::class,
            'aset-tetap-lainnya' => \App\Models\AsetTetapLainnya::class,
        ];

        $class = $map[$this->asset_type] ?? null;

        if (! $class) {
            return null;
        }

        return $this->belongsTo($class, 'asset_id');
    }

    public function getAssetAttribute()
    {
        try {
            $relation = $this->asset();
            if ($relation instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                return $relation->getResults();
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }
}
