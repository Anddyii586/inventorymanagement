<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'tanggal_laporan' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            $request->ticket_number = 'MT-' . date('Ymd') . '-' . strtoupper(uniqid());
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function maintenanceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function maintenanceLog()
    {
        return $this->hasOne(MaintenanceLog::class);
    }
}
