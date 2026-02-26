<?php

namespace App\Filament\Widgets;

use App\Models\PeralatanMesin;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class AssetConditionTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Detail Kondisi Aset';
    
    protected int | string | array $columnSpan = [
        'default' => 1,
        'md' => 4,
        'lg' => 12,
    ];

    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.pages.kondisi-aset');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(PeralatanMesin::query())
            ->headerActions([
                \Filament\Tables\Actions\Action::make('cetak_laporan')
                    ->label('Cetak Laporan')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->action(function () {
                        // Action is handled via URL since we open in new tab
                    })
                    ->url(fn (\App\Filament\Widgets\AssetConditionTableWidget $livewire) => route('assets.print-condition-report', [
                        'kondisi' => $livewire->tableFilters['kondisi']['value'] ?? null,
                        'search' => $livewire->getTableSearch(),
                    ]))
                    ->openUrlInNewTab()
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('ID Aset')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kode_lokasi')
                    ->label('Kode Lokasi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_barang')
                    ->label('Nama Aset')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kondisi')
                    ->label('Kondisi')
                    ->formatStateUsing(fn ($state) => $state ?: 'Tidak Diketahui')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Baik' => 'success',
                        'Rusak Ringan', 'Kurang Baik' => 'warning',
                        'Rusak', 'Rusak Berat' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                
                TextColumn::make('harga')
                    ->label('Nilai Aset')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('kondisi')
                    ->options([
                        'Baik' => 'Baik',
                        'Kurang Baik' => 'Kurang Baik',
                        'Rusak Berat' => 'Rusak Berat',
                    ]),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('lihat_data')
                    ->label('Lihat Detail')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->url(fn (PeralatanMesin $record): string => \App\Filament\Resources\PeralatanMesinResource::getUrl('view', ['record' => $record]))
            ])
            ->striped()
            ->paginated([10, 25, 50])
            ->defaultSort('id', 'desc');
    }

    public function getTableRecordKey($record): string
    {
        return (string) $record->id;
    }
}
