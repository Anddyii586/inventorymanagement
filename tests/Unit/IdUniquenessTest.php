<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\KodifikasiService;

class IdUniquenessTest extends TestCase
{
    public function test_kodifikasi_service_generates_consistent_ids_when_no_database()
    {
        // Test that the service generates consistent IDs when no database exists
        $id1 = KodifikasiService::kodeBarang(null, '03.01.01.01', '2023-01-01', \App\Models\PeralatanMesin::class);
        $id2 = KodifikasiService::kodeBarang(null, '03.01.01.01', '2023-01-01', \App\Models\PeralatanMesin::class);
        
        // When no database exists, both should return the same initial ID
        $this->assertEquals($id1, $id2);
        $this->assertStringStartsWith('03.01.01.01.23.', $id1);
        $this->assertStringStartsWith('03.01.01.01.23.', $id2);
        $this->assertEquals('03.01.01.01.23.0001', $id1);
    }

    public function test_kodifikasi_service_handles_null_parameters()
    {
        // Test that the service handles null parameters gracefully
        $id = KodifikasiService::kodeBarang(null, null, null, \App\Models\PeralatanMesin::class);
        $this->assertNull($id);
    }

    public function test_kodifikasi_service_handles_invalid_date()
    {
        // Test that the service handles invalid dates gracefully
        $id = KodifikasiService::kodeBarang(null, '03.01.01.01', 'invalid-date', \App\Models\PeralatanMesin::class);
        $this->assertNull($id);
    }

    public function test_kodifikasi_service_returns_existing_id_for_record()
    {
        // Mock a record with existing ID
        $record = new \App\Models\PeralatanMesin();
        $record->id = '03.01.01.01.23.0001';
        
        $id = KodifikasiService::kodeBarang($record, '03.01.01.01', '2023-01-01', \App\Models\PeralatanMesin::class);
        $this->assertEquals('03.01.01.01.23.0001', $id);
    }

    public function test_kodifikasi_service_generates_different_ids_for_different_years()
    {
        // Test that different years generate different IDs
        $id1 = KodifikasiService::kodeBarang(null, '03.01.01.01', '2023-01-01', \App\Models\PeralatanMesin::class);
        $id2 = KodifikasiService::kodeBarang(null, '03.01.01.01', '2024-01-01', \App\Models\PeralatanMesin::class);
        
        $this->assertNotEquals($id1, $id2);
        $this->assertStringContainsString('23', $id1);
        $this->assertStringContainsString('24', $id2);
    }
} 