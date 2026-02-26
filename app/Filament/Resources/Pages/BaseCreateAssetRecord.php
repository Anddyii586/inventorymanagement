<?php

namespace App\Filament\Resources\Pages;

use App\Services\KodifikasiService;
use Filament\Resources\Pages\CreateRecord;

abstract class BaseCreateAssetRecord extends CreateRecord
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure ID is unique before creating
        if (isset($data['id'])) {
            $originalId = $data['id'];
            $counter = 1;
            $modelClass = $this->getModel();
            
            // If ID already exists, increment the registration number
            while ($modelClass::where('id', $data['id'])->exists()) {
                $parts = explode('.', $originalId);
                if (count($parts) >= 3) {
                    $registrationNumber = sprintf("%04d", intval($parts[2]) + $counter);
                    $data['id'] = $parts[0] . '.' . $parts[1] . '.' . $registrationNumber;
                } else {
                    // Fallback: generate new ID using KodifikasiService
                    $data['id'] = KodifikasiService::kodeBarang(
                        null, 
                        $data['sub_sub_kelompok_id'] ?? null, 
                        $data['tanggal_pengadaan'] ?? null, 
                        $modelClass
                    );
                    break;
                }
                $counter++;
            }
        }
        
        return $data;
    }

    public function getModel(): string
    {
        return $this->getResource()::getModel();
    }
} 