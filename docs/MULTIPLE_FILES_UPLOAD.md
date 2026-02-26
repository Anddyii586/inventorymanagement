# Multiple Files Upload Implementation

Dokumen ini menjelaskan implementasi multiple files upload pada sistem aset menggunakan struktur database yang ada.

## Latar Belakang

Sistem aset saat ini menggunakan kolom `dokumentasi` dengan tipe `string` untuk menyimpan file. Dengan implementasi ini, kita bisa menyimpan multiple files dalam satu kolom menggunakan format array/JSON.

## Implementasi

### 1. Model Changes

#### PeralatanMesin Model
```php
protected $casts = [
    'dokumentasi' => 'array'
];

// Helper methods
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

public function hasDokumentasi()
{
    return !empty($this->dokumentasi_files);
}

public function getDokumentasiCountAttribute()
{
    return count($this->dokumentasi_files);
}
```

#### Tanah Model
Implementasi yang sama seperti PeralatanMesin.

### 2. Resource Changes

#### FileUpload Configuration
```php
Forms\Components\FileUpload::make('dokumentasi')
    ->disk('minio')
    ->directory('peralatan-mesin')
    ->multiple()
    ->maxFiles(10)
    ->maxSize(10240) // 10MB per file
    ->acceptedFileTypes([
        'image/*', 
        'application/pdf', 
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ])
    ->openable()
    ->downloadable()
    ->preserveFilenames()
    ->columnSpanFull()
```

### 3. Controller Changes

#### PublicAssetController
```php
public function showPeralatanMesin($id)
{
    $peralatanMesin = PeralatanMesin::with(['wilayah', 'subBidang', 'unit', 'subSubKelompok', 'ruangan'])->findOrFail($id);

    // Get images from dokumentasi if exists
    $images = [];
    $documents = [];
    
    if ($peralatanMesin->hasDokumentasi()) {
        foreach ($peralatanMesin->dokumentasi_files as $file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $fileUrl = Storage::disk('minio')->url($file);
            
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                $images[] = $fileUrl;
            } else {
                $documents[] = [
                    'name' => basename($file),
                    'url' => $fileUrl,
                    'type' => $extension
                ];
            }
        }
    }

    return view('public.peralatan-mesin.detail', compact('peralatanMesin', 'images', 'documents'));
}
```

## Cara Kerja

### 1. Data Storage
- **Single File (Legacy)**: `"file1.jpg"`
- **Multiple Files (New)**: `["file1.jpg", "file2.pdf", "file3.png"]`

### 2. Laravel Cast
- `'dokumentasi' => 'array'` akan otomatis:
  - **Save**: Convert array ke JSON string
  - **Retrieve**: Convert JSON string ke array

### 3. Backward Compatibility
- Method `getDokumentasiFilesAttribute()` handle kedua format
- Legacy single file tetap bisa diakses

## Keuntungan

### ✅ **Dengan Struktur Saat Ini:**
- **Tidak perlu migration** - menggunakan kolom yang ada
- **Backward compatible** - data lama tetap bisa diakses
- **Laravel handle otomatis** - cast array ke JSON
- **Flexible** - bisa simpan unlimited files (dalam batas VARCHAR/TEXT)

### 📊 **Perbandingan Storage:**

| Format | Contoh Data | Ukuran | Kompleksitas |
|--------|-------------|--------|--------------|
| **Single File** | `"file.jpg"` | 10 bytes | Rendah |
| **Multiple Files** | `["file1.jpg","file2.pdf"]` | 30 bytes | Sedang |
| **JSON Complex** | `[{"name":"file.jpg","size":1024}]` | 50 bytes | Tinggi |

## Penggunaan

### Di Blade View
```php
{{-- Display images --}}
@if(!empty($images))
    <div class="image-gallery">
        @foreach($images as $image)
            <img src="{{ $image }}" alt="Dokumentasi">
        @endforeach
    </div>
@endif

{{-- Display documents --}}
@if(!empty($documents))
    <div class="document-list">
        @foreach($documents as $doc)
            <a href="{{ $doc['url'] }}" target="_blank">
                {{ $doc['name'] }} ({{ strtoupper($doc['type']) }})
            </a>
        @endforeach
    </div>
@endif
```

### Di Filament Table
```php
Tables\Columns\TextColumn::make('dokumentasi_count')
    ->label('Jumlah File')
    ->badge()
    ->color('success')
```

## Konfigurasi

### File Types yang Diizinkan
- **Images**: `jpg`, `jpeg`, `png`, `gif`
- **Documents**: `pdf`, `doc`, `docx`
- **Custom**: Bisa ditambah sesuai kebutuhan

### Limits
- **Max Files**: 10 files per aset
- **Max Size**: 10MB per file
- **Total Size**: 100MB per aset

## Troubleshooting

### Error: "Array to string conversion"
**Solusi**: Pastikan model sudah menggunakan cast `'dokumentasi' => 'array'`

### Error: "JSON decode failed"
**Solusi**: Data legacy perlu di-migrate atau handle dengan method `getDokumentasiFilesAttribute()`

### Performance Issue
**Solusi**: 
- Limit jumlah file
- Implement lazy loading untuk gallery
- Use pagination untuk banyak file

## Migration Strategy

### Phase 1: Implement Multiple Files
- Update models dengan cast
- Update resources dengan multiple upload
- Test dengan data baru

### Phase 2: Migrate Legacy Data
- Backup database
- Convert single file ke array format
- Update views untuk handle multiple files

### Phase 3: Optimize
- Implement file compression
- Add file validation
- Optimize storage usage

## Monitoring

### Database Monitoring
```sql
-- Check file count distribution
SELECT 
    CASE 
        WHEN dokumentasi IS NULL THEN 'No Files'
        WHEN JSON_VALID(dokumentasi) THEN 'Multiple Files'
        ELSE 'Single File'
    END as file_type,
    COUNT(*) as count
FROM golongan_peralatan_mesin 
GROUP BY file_type;
```

### Storage Monitoring
- Monitor MinIO storage usage
- Track file upload/download metrics
- Alert jika storage > 80%

## Best Practices

1. **Always validate file types** sebelum upload
2. **Implement file size limits** untuk mencegah abuse
3. **Use meaningful file names** untuk memudahkan search
4. **Regular backup** untuk file storage
5. **Monitor storage usage** secara berkala 