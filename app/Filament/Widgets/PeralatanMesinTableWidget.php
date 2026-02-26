<?php

namespace App\Filament\Widgets;

use App\Models\PeralatanMesin;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class PeralatanMesinTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Rekap Peralatan & Mesin per Kategori';
    protected static ?int $sort = 100;
    
    protected int | string | array $columnSpan = [
        'default' => 1,
        'md' => 4,
        'lg' => 12,
    ];

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Tables\Actions\Action::make('Lihat Data Lengkap')
                ->url(\App\Filament\Resources\PeralatanMesinResource::getUrl())
                ->icon('heroicon-m-arrow-right')
                ->iconPosition('after')
                ->button()
                ->color('primary'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PeralatanMesin::query()
                    ->selectRaw('
                        golongan_peralatan_mesin.sub_sub_kelompok_id,
                        asset_sub_sub_kelompok.id as kategori_id,
                        asset_sub_sub_kelompok.sub_sub_kelompok as kategori_nama,
                        count(*) as total,
                        sum(golongan_peralatan_mesin.harga) as total_value
                    ')
                    ->join('asset_sub_sub_kelompok', 'golongan_peralatan_mesin.sub_sub_kelompok_id', '=', 'asset_sub_sub_kelompok.id')
                    ->groupBy('golongan_peralatan_mesin.sub_sub_kelompok_id', 'asset_sub_sub_kelompok.id', 'asset_sub_sub_kelompok.sub_sub_kelompok')
                    ->orderByDesc('total')
            )
            ->columns([
                TextColumn::make('kategori_id')
                    ->label('Kode Kategori')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('kategori_nama')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                TextColumn::make('total')
                    ->label('Jumlah')
                    ->sortable()
                    ->alignCenter(),
                
                TextColumn::make('total_value')
                    ->label('Total Nilai')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),
                
                TextColumn::make('average_value')
                    ->label('Rata-rata Nilai')
                    ->getStateUsing(function ($record) {
                        return $record->total > 0 ? $record->total_value / $record->total : 0;
                    })
                    ->money('IDR')
                    ->alignEnd(),
            ])
            ->striped()
            ->paginated([10, 25, 50])
            ->defaultSort('total', 'desc')
            ->recordUrl(null); // Disable record URLs since this is aggregated data
    }

    public function getTableRecordKey($record): string
    {
        // Use kategori_id as the record key for aggregated data
        return $record->kategori_id ?? 'unknown';
    }
    
} 