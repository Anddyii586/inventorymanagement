<?php

namespace App\Models\Traits;

use App\Services\KodifikasiService;

trait HasUniqueId
{
    protected static function bootHasUniqueId()
    {
        static::creating(function ($model) {
            // Ensure ID is unique before creating
            if ($model->id) {
                $originalId = $model->id;
                $counter = 1;
                
                // If ID already exists, increment the registration number
                while (static::where('id', $model->id)->exists()) {
                    $parts = explode('.', $originalId);
                    if (count($parts) >= 3) {
                        $registrationNumber = sprintf("%04d", intval($parts[2]) + $counter);
                        $model->id = $parts[0] . '.' . $parts[1] . '.' . $registrationNumber;
                    } else {
                        // Fallback: generate new ID using KodifikasiService
                        $model->id = KodifikasiService::kodeBarang(
                            $model, 
                            $model->sub_sub_kelompok_id, 
                            $model->tanggal_pengadaan, 
                            static::class
                        );
                        break;
                    }
                    $counter++;
                }
            }
        });
    }
} 