<?php

/**
 * Contoh Test Multiple Files Upload
 * 
 * File ini berisi contoh cara test implementasi multiple files upload
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\PeralatanMesin;
use App\Models\Tanah;

// Test 1: Simulasi data legacy (single file)
echo "=== Test 1: Legacy Single File ===\n";
$peralatanLegacy = new PeralatanMesin();
$peralatanLegacy->dokumentasi = "old-file.jpg";

echo "Original: " . $peralatanLegacy->dokumentasi . "\n";
echo "Files Array: " . json_encode($peralatanLegacy->dokumentasi_files) . "\n";
echo "Has Dokumentasi: " . ($peralatanLegacy->hasDokumentasi() ? 'Yes' : 'No') . "\n";
echo "Count: " . $peralatanLegacy->dokumentasi_count . "\n\n";

// Test 2: Simulasi data baru (multiple files)
echo "=== Test 2: Multiple Files ===\n";
$peralatanNew = new PeralatanMesin();
$peralatanNew->dokumentasi = [
    "file1.jpg",
    "document.pdf", 
    "image.png",
    "report.docx"
];

echo "Original: " . json_encode($peralatanNew->dokumentasi) . "\n";
echo "Files Array: " . json_encode($peralatanNew->dokumentasi_files) . "\n";
echo "Has Dokumentasi: " . ($peralatanNew->hasDokumentasi() ? 'Yes' : 'No') . "\n";
echo "Count: " . $peralatanNew->dokumentasi_count . "\n\n";

// Test 3: Simulasi data kosong
echo "=== Test 3: Empty Data ===\n";
$peralatanEmpty = new PeralatanMesin();
$peralatanEmpty->dokumentasi = null;

echo "Original: " . ($peralatanEmpty->dokumentasi ?? 'NULL') . "\n";
echo "Files Array: " . json_encode($peralatanEmpty->dokumentasi_files) . "\n";
echo "Has Dokumentasi: " . ($peralatanEmpty->hasDokumentasi() ? 'Yes' : 'No') . "\n";
echo "Count: " . $peralatanEmpty->dokumentasi_count . "\n\n";

// Test 4: Simulasi save dan retrieve dari database
echo "=== Test 4: Database Simulation ===\n";

// Simulasi save ke database (akan di-cast ke JSON)
$peralatanTest = new PeralatanMesin();
$peralatanTest->dokumentasi = ["test1.jpg", "test2.pdf"];

// Simulasi data yang tersimpan di database (JSON string)
$jsonData = json_encode($peralatanTest->dokumentasi);
echo "Saved to DB: " . $jsonData . "\n";

// Simulasi retrieve dari database
$retrievedData = json_decode($jsonData, true);
echo "Retrieved from DB: " . json_encode($retrievedData) . "\n";

// Test 5: File type classification
echo "=== Test 5: File Type Classification ===\n";
$files = [
    "photo.jpg",
    "document.pdf", 
    "image.png",
    "report.docx",
    "data.xlsx"
];

foreach ($files as $file) {
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
    
    echo "$file -> " . ($isImage ? 'IMAGE' : 'DOCUMENT') . " ($extension)\n";
}

echo "\n=== Test Selesai ===\n"; 