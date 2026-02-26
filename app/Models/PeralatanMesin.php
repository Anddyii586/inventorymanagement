<?php

namespace App\Models;

use App\Models\Lokasi\Unit;
use App\Models\Lokasi\Ruangan;
use App\Models\Lokasi\Wilayah;
use App\Models\Lokasi\SubBidang;
use App\Models\Aset\SubSubKelompok;
use App\Models\Traits\HasUniqueId;
use Illuminate\Database\Eloquent\Model;

class PeralatanMesin extends Model
{
    use HasUniqueId;
    use Traits\HasDepreciation;

    protected $table = 'golongan_peralatan_mesin';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = true;

    protected $casts = [
        'dokumentasi' => 'array',
        'tahun_pembelian' => 'integer',
        'rtu' => 'boolean',
        'panel_listrik' => 'boolean',
        'rumah_panel' => 'boolean'
    ];

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id', 'id');
    }

    public function subBidang()
    {
        return $this->belongsTo(SubBidang::class, 'sub_bidang_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function subSubKelompok()
    {
        return $this->belongsTo(SubSubKelompok::class, 'sub_sub_kelompok_id', 'id');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id', 'id');
    }

    /**
     * Get dokumentasi files as array
     */
    public function getDokumentasiFilesAttribute()
    {
        if (!$this->dokumentasi) {
            return [];
        }
        
        if (is_string($this->dokumentasi)) {
            // Handle legacy single file format
            return [$this->dokumentasi];
        }
        
        return $this->dokumentasi;
    }

    /**
     * Check if has dokumentasi
     */
    public function hasDokumentasi()
    {
        return !empty($this->dokumentasi_files);
    }

    /**
     * Get dokumentasi count
     */
    public function getDokumentasiCountAttribute()
    {
        return count($this->dokumentasi_files);
    }
    /**
     * Get maintenance logs
     */
    public function maintenanceLogs()
    {
        return $this->morphMany(MaintenanceLog::class, 'maintenanceable');
    }

    /**
     * Get maintenance schedules
     */
    public function maintenanceSchedules()
    {
        return $this->morphMany(MaintenanceSchedule::class, 'maintenanceable');
    }
}
