# 🗂️ DASHBOARD FILE STRUCTURE & CHANGES MAP

## 📍 File Location Map

```
PROJECT ROOT
c:\laragon\www\asset-main\
│
├─ app/                                    ← PHP Logic Layer
│  │
│  ├─ Filament/                           ← Filament-specific files
│  │  │
│  │  ├─ Pages/
│  │  │  └─ Dashboard.php ✏️ DIUBAH       ← Widget orchestration + title
│  │  │     ├─ getHeaderWidgets()         ← Returns: [PeralatanMesinSummaryWidget]
│  │  │     ├─ getFooterWidgets()         ← Returns: [BarChart, PieChart, Table, Download]
│  │  │     ├─ getHeaderWidgetsColumns()  ← 1 kolom (full width)
│  │  │     └─ getFooterWidgetsColumns()  ← 2 kolom (new method)
│  │  │
│  │  └─ Widgets/                         ← Dashboard components
│  │     ├─ PeralatanMesinSummaryWidget.php       (6 stats cards)
│  │     ├─ PeralatanMesinBidangBarChartWidget.php ✏️ DIUBAH (bar chart enhanced)
│  │     │  ├─ Heading → "Nilai & Jumlah Item per Bidang"
│  │     │  ├─ Description → "Analisis aset berdasarkan departemen/bidang"
│  │     │  ├─ Legend → top position (enhanced)
│  │     │  ├─ Tooltip → interactive with styling
│  │     │  └─ Grid → subtle color rgba
│  │     │
│  │     ├─ PeralatanMesinChartWidget.php ✏️ DIUBAH (pie chart enhanced)
│  │     │  ├─ Heading → "Distribusi Peralatan & Mesin per Kategori"
│  │     │  ├─ Description → "Visualisasi jumlah item berdasarkan kategori"
│  │     │  ├─ Tooltip → interactive with styling
│  │     │  ├─ Legend → point style (enhanced)
│  │     │  └─ Height → 400px
│  │     │
│  │     ├─ PeralatanMesinTableWidget.php         (data table)
│  │     ├─ ManualBookDownloadWidget.php          (download button)
│  │     └─ CustomAccountWidget.php               (account info)
│  │
│  └─ Providers/
│     └─ Filament/
│        └─ AdminPanelProvider.php ✏️ DIUBAH      ← Panel configuration
│           ├─ brandName('Management Aset') ← NEW
│           ├─ colors['primary'] → Indigo ← CHANGED (from Amber)
│           └─ ...rest config
│
├─ resources/                              ← Frontend Layer
│  │
│  ├─ views/
│  │  └─ filament/
│  │     ├─ pages/
│  │     │  └─ dashboard.blade.php ✏️ DIUBAH     ← Template view
│  │     │     ├─ Header section (NEW)
│  │     │     │  ├─ <h1> "Management Aset"
│  │     │     │  └─ <p> Deskripsi
│  │     │     ├─ Header widgets wrapper (NEW div)
│  │     │     └─ Footer widgets wrapper (NEW div)
│  │     │
│  │     └─ widgets/
│  │        └─ manual-book-download-widget.blade.php
│  │
│  └─ css/
│     └─ app.css                           ← Tailwind CSS (tidak perlu diubah)
│
├─ config/
│  └─ filament.php                         ← Global Filament config
│
├─ docs/                                   ← Documentation (NEW)
│  ├─ DASHBOARD_IMPROVEMENT.md ✨ BARU    ← Lengkap & detail
│  ├─ DASHBOARD_CHANGES_SUMMARY.md ✨ BARU ← Technical summary
│  ├─ DASHBOARD_QUICK_REFERENCE.md ✨ BARU ← Quick guide
│  └─ DASHBOARD_BEFORE_AFTER.md ✨ BARU   ← Comparison
│
├─ DASHBOARD_COMPLETE.md ✨ BARU          ← Ringkasan final
├─ tailwind.config.js
├─ vite.config.js
└─ composer.json
```

---

## 🔗 File Relationships & Data Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    HTTP Request to /admin                       │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│         AdminPanelProvider.php (Panel Configuration)             │
│  ├─ brandName: 'Management Aset'                                │
│  ├─ colors['primary']: Indigo                                   │
│  └─ discoverWidgets(...)                                        │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│              Dashboard.php (Page Logic)                          │
│  ├─ $title: 'Management Aset'                                  │
│  ├─ $view: 'filament.pages.dashboard'                          │
│  │
│  ├─ getHeaderWidgets()                                          │
│  │  └─ Returns: [PeralatanMesinSummaryWidget]                  │
│  │
│  ├─ getFooterWidgets()                                          │
│  │  └─ Returns: [BarChart, PieChart, Table, Download]          │
│  │
│  ├─ getHeaderWidgetsColumns() → ['default'=>1, 'sm'=>1, 'lg'=>1]
│  └─ getFooterWidgetsColumns() → ['default'=>1, 'sm'=>2, 'lg'=>2]
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│        dashboard.blade.php (Template Rendering)                 │
│                                                                 │
│  <h1>Management Aset</h1>  ← Title besar                       │
│  <p>Dashboard pusat...</p> ← Deskripsi                         │
│                                                                 │
│  ┌─ Header Widgets (1 kolom) ──────────────────────┐           │
│  │  [PeralatanMesinSummaryWidget]                   │           │
│  │  (6 statistics cards)                            │           │
│  └──────────────────────────────────────────────────┘           │
│                                                                 │
│  ┌─ Footer Widgets (2 kolom) ──────────────────────┐           │
│  │ [BarChart]     │ [PieChart]                      │           │
│  │ [Table]        │ [Download]                      │           │
│  └──────────────────────────────────────────────────┘           │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ↓
        ┌─────────────────────────────────┐
        │     Widgets Rendering           │
        │                                 │
        ├─ PeralatanMesinBidangBarChartWidget.php
        │  ├─ getData() → Query DB
        │  ├─ getType() → 'bar'
        │  └─ getOptions() → Legend(top) + Tooltip(enhanced)
        │
        ├─ PeralatanMesinChartWidget.php
        │  ├─ getData() → Query DB
        │  ├─ getType() → 'doughnut'
        │  └─ getOptions() → Legend(bottom) + Tooltip(enhanced)
        │
        ├─ PeralatanMesinTableWidget.php
        │  └─ Render data table
        │
        └─ ManualBookDownloadWidget.php
           └─ Render download button
```

---

## 📊 Widget Dependency Diagram

```
Dashboard.php
│
├─ getHeaderWidgets()
│  └─ PeralatanMesinSummaryWidget
│     ├─ Stat::make('Total Peralatan...')
│     ├─ Stat::make('Total Nilai Aset...')
│     ├─ Stat::make('Rata-rata Nilai...')
│     ├─ Stat::make('Kondisi Baik...')
│     ├─ Stat::make('Kondisi Kurang Baik...')
│     └─ Stat::make('Kondisi Rusak Berat...')
│
└─ getFooterWidgets()
   ├─ PeralatanMesinBidangBarChartWidget ✏️
   │  ├─ getData()
   │  │  └─ Query: PeralatanMesin (grouped by bidang)
   │  └─ getOptions() ← Legend(top), Tooltip(enhanced)
   │
   ├─ PeralatanMesinChartWidget ✏️
   │  ├─ getData()
   │  │  └─ Query: PeralatanMesin (grouped by kategori)
   │  └─ getOptions() ← Legend(bottom), Tooltip(enhanced)
   │
   ├─ PeralatanMesinTableWidget
   │  └─ getData()
   │     └─ Query: PeralatanMesin (with filtering)
   │
   └─ ManualBookDownloadWidget
      └─ Render download form
```

---

## 🎨 CSS & Styling Hierarchy

```
Tailwind CSS (Global)
  ↓
tailwind.config.js (Theme config)
  ├─ colors: extended
  ├─ fontFamily: custom
  └─ plugins: []
  ↓
resources/css/app.css (Main CSS)
  ├─ @tailwind base;
  ├─ @tailwind components;
  └─ @tailwind utilities;
  ↓
Dashboard Template (Inline Classes)
  ├─ text-3xl font-bold (H1)
  ├─ text-gray-900 dark:text-white
  ├─ mb-6, mb-8 (spacing)
  └─ mt-2 (margin)
  ↓
Chart Options (JS Config)
  ├─ backgroundColor: rgba(...)
  ├─ font: { size: 12 }
  ├─ padding: 10
  └─ borderColor: rgba(...)
```

---

## 📁 Changed Files Summary Table

| # | File | Type | Changes | Lines |
|---|------|------|---------|-------|
| 1 | `app/Filament/Pages/Dashboard.php` | PHP Class | Title, widgets order, columns method | ~48 |
| 2 | `resources/views/filament/pages/dashboard.blade.php` | Blade Template | Header section, styling | ~28 |
| 3 | `app/Filament/Widgets/PeralatanMesinChartWidget.php` | PHP Widget | Heading, description, options | ~35 |
| 4 | `app/Filament/Widgets/PeralatanMesinBidangBarChartWidget.php` | PHP Widget | Heading, description, options | ~50 |
| 5 | `app/Providers/Filament/AdminPanelProvider.php` | PHP Provider | brandName, color | 2 lines |

**Total Changes:** ~163 lines of modified/added code

---

## 🔄 Data Flow for Chart

```
Database (MySQL)
├─ peralachatan_mesin table
├─ struktur_bidang table
└─ asset_sub_sub_kelompok table
        ↓
PeralatanMesin Model
├─ Queries for Bar Chart
│  └─ selectRaw() → Group by bidang → Sum nilai
├─ Queries for Pie Chart
│  └─ selectRaw() → Group by kategori → Count items
└─ Queries for Table
   └─ all() → Get all records
        ↓
Widget getData() method
├─ Process data
├─ Format for Chart.js
└─ Return arrays (labels, datasets)
        ↓
getOptions() method
├─ Configure chart type
├─ Set tooltip styling
├─ Set legend positioning
├─ Set colors & fonts
└─ Return configuration
        ↓
Chart.js (Frontend)
├─ Render SVG/Canvas
├─ Add interactivity
├─ Handle hover (tooltip)
└─ Display legend
        ↓
User Browser Display
└─ Interactive chart visible
```

---

## 🎯 Modification Points for Future Changes

```
Want to add new widget?
  → Edit app/Filament/Pages/Dashboard.php
     → Add to getFooterWidgets()
     → Adjust getFooterWidgetsColumns() if needed

Want to change chart colors?
  → Edit app/Filament/Widgets/PeralatanMesinChartWidget.php
     → Modify backgroundColor array in getData()

Want to change tooltip style?
  → Edit getOptions() method
     → Modify 'tooltip' config

Want to change responsive breakpoints?
  → Edit getHeaderWidgetsColumns() or getFooterWidgetsColumns()
     → Change 'default', 'sm', 'lg' values

Want to change header text?
  → Edit resources/views/filament/pages/dashboard.blade.php
     → Modify H1 text content

Want to change brand color?
  → Edit app/Providers/Filament/AdminPanelProvider.php
     → Change Color::Indigo to other color
```

---

## 🔐 Security & Integrity

```
✅ No SQL injection risks (using Eloquent)
✅ No XSS risks (Blade auto-escaping)
✅ No CSRF issues (Filament middleware)
✅ Authorization: Keep existing (not modified)
✅ Authentication: Keep existing (not modified)
✅ Database migrations: None needed
```

---

## 📋 Testing Coverage

```
Unit Tests: Not needed (config only)
Integration Tests: 
  ✓ Dashboard renders
  ✓ Widgets load data
  ✓ Charts display

Manual Tests:
  ✓ Header visible
  ✓ Stats cards display
  ✓ Charts responsive
  ✓ Tooltip hover works
  ✓ Legend toggle works
  ✓ Mobile layout correct
  ✓ Dark mode support
```

---

**📍 All file locations, relationships, and modification points documented!**
