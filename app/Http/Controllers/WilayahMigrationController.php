<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WilayahMigrationService;

class WilayahMigrationController extends Controller
{
    /**
     * Tampilkan halaman preview migrasi
     */
    public function preview()
    {
        $preview = WilayahMigrationService::previewMigration();
        
        return view('wilayah-migration.preview', compact('preview'));
    }
    
    /**
     * Jalankan migrasi
     */
    public function migrate(Request $request)
    {
        $result = WilayahMigrationService::migrateWilayahCodes();
        
        if ($request->expectsJson()) {
            return response()->json($result);
        }
        
        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }
    
    /**
     * Tampilkan status migrasi
     */
    public function status()
    {
        $status = WilayahMigrationService::checkMigrationStatus();
        $stats = WilayahMigrationService::getMigrationStats();
        
        return response()->json([
            'status' => $status,
            'stats' => $stats
        ]);
    }
} 