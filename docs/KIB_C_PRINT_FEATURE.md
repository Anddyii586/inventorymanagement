# KIB C Print Feature - Gedung & Bangunan

## Overview
This feature allows users to print KIB C (Kartu Inventaris Barang C) for buildings and structures in the asset management system.

## Features

### Print KIB C
- **Location**: Admin Panel → Gedung & Bangunan → "Cetak KIB" button
- **Functionality**: Generates a printable KIB C report for buildings and structures
- **Format**: HTML page optimized for printing with proper table layout

### Filtering Options
1. **Manual Input Mode**:
   - Direct kode lokasi input
   - Quick access for known location codes

2. **Dynamic Filtering**:
   - Wilayah (Region)
   - Bidang (Field)
   - Sub Bidang (Sub-field)
   - Unit
   - Tahun (Year)

3. **Additional Options**:
   - Penanggung Jawab (Person in Charge) selection
   - Automatic kode lokasi generation based on selected filters

## Table Structure
The KIB C print includes a 20-column table with the following structure:

1. **No** - Row number
2. **Jenis Barang/Nama Barang** - Goods type/name
3. **Kode Barang** - Goods code
4. **Register** - Registration number
5. **Kondisi B** - Good condition
6. **Kondisi KB** - Fair condition  
7. **Kondisi RB** - Poor condition
8. **Bertingkat/Tidak** - Multi-story or not
9. **Beton/Tidak** - Concrete or not
10. **Luas Lantai (m²)** - Floor area
11. **Letak/Alamat** - Location/address
12. **Tahun Pengadaan** - Year of procurement
13. **Luas Tanah (m²)** - Land area
14. **Status Tanah** - Land status
15. **Nomor Sertifikat Tanah** - Land certificate number
16. **Asal-usul Tanah** - Land origin
17. **Harga (Rp.)** - Price
18. **PIC** - Person in charge
19. **Dokumentasi** - Documentation availability
20. **Keterangan** - Notes/remarks

## Administrative Information
The print includes administrative details at the top:
- DIREKTORAT
- BIDANG  
- SUB BIDANG
- LOKASI UNIT KERJA
- KODE LOKASI
- KIB (automatically set to "C")

## Signature Section
The print includes signature spaces for:
- Mengetahui (Manager Aset)
- Penanggung Jawab (Person in Charge)
- Dibuat Oleh (Created By)

## Technical Implementation

### Files Created/Modified:
1. **Controller**: `app/Http/Controllers/GedungBangunanController.php`
2. **Route**: Added to `routes/web.php`
3. **View**: `resources/views/filament/resources/gedung-bangunan-resource/kib-cetak.blade.php`
4. **Resource Page**: Modified `app/Filament/Resources/GedungBangunanResource/Pages/ListGedungBangunan.php`

### Route:
```
GET /gedung-bangunan/cetak-kib
```

### Usage:
1. Navigate to Admin Panel → Gedung & Bangunan
2. Click "Cetak KIB" button in the header
3. Configure filters as needed
4. Click "Submit" to generate the print view
5. Use browser print function (Ctrl+P / Cmd+P) to print

## Dependencies
- Bootstrap 5.3.0 (for styling)
- Laravel Filament (for admin interface)
- Existing asset models and relationships

## Browser Compatibility
- Modern browsers with CSS Grid and Flexbox support
- Optimized for A4 paper printing
- Responsive design for different screen sizes 