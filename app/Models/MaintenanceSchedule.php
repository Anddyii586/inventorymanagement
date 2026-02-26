<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MaintenanceSchedule extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_terakhir' => 'date',
        'tanggal_berikutnya' => 'date',
        'is_aktif' => 'boolean',
        'enable_notifikasi' => 'boolean',
        'notifikasi_hari_sebelumnya' => 'integer',
    ];

    /**
     * Check if schedule is overdue
     */
    public function isOverdue(): bool
    {
        return $this->tanggal_berikutnya->lt(now()->startOfDay());
    }

    /**
     * Check if reminder should be sent today
     */
    public function shouldSendReminder(): bool
    {
        if (!$this->is_aktif || !$this->enable_notifikasi) {
            return false;
        }

        $today = now()->startOfDay();
        $dueDate = $this->tanggal_berikutnya;
        $notificationDate = $dueDate->copy()->subDays($this->notifikasi_hari_sebelumnya)->startOfDay();

        return $today->gte($notificationDate) && $today->lt($dueDate);
    }

    /**
     * Scope for schedules needing reminder
     */
    public function scopeNeedingReminder($query)
    {
        return $query->where('is_aktif', true)
            ->where('enable_notifikasi', true)
            ->whereDate('tanggal_berikutnya', '>=', now());
    }

    public function maintenanceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
