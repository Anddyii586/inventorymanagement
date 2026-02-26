<?php

namespace App\Filament\Resources\GedungBangunanResource\Pages;

use App\Filament\Resources\GedungBangunanResource;
use Filament\Actions;
use Filament\Forms;
use App\Services\KodifikasiService;
use Filament\Resources\Pages\ListRecords;

class ListGedungBangunan extends ListRecords
{
    protected static string $resource = GedungBangunanResource::class;

    protected function getHeaderActions(): array
    {
        $kodeLokasi = fn($set, $get) => $set('kode_lokasi', KodifikasiService::kodeLokasi($get('wilayah_id'), $get('sub_bidang_id'), $get('unit_id'), $get('tahun')));

        // Form KIB - digunakan untuk cetak dan export
        $kibForm = [
            Forms\Components\Select::make('penanggung_jawab')
                ->label('Penanggung Jawab')
                ->options(function () {
                    return \App\Models\User::pluck('nama', 'id')->toArray();
                })
                ->searchable()
                ->placeholder('Pilih Penanggung Jawab')
                ->nullable(),
            Forms\Components\Toggle::make('input_mode')
                ->label('Input Manual')
                ->default(false)
                ->live(),
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Select::make('wilayah_id')
                        ->label('Wilayah')
                        ->options(\App\Models\Lokasi\Wilayah::pluck('wilayah', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Wilayah')
                        ->live()
                        ->afterStateUpdated($kodeLokasi)
                        ->columnSpanFull(),
                    Forms\Components\Select::make('bidang_id')
                        ->label('Bidang')
                        ->options(\App\Models\Lokasi\Bidang::pluck('bidang', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Bidang')
                        ->live()
                        ->afterStateUpdated($kodeLokasi),
                    Forms\Components\Select::make('sub_bidang_id')
                        ->label('Sub Bidang')
                        ->options(\App\Models\Lokasi\SubBidang::pluck('sub_bidang', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Sub Bidang')
                        ->live()
                        ->afterStateUpdated($kodeLokasi),
                    Forms\Components\Select::make('unit_id')
                        ->label('Unit')
                        ->options(\App\Models\Lokasi\Unit::pluck('unit', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Unit')
                        ->live()
                        ->afterStateUpdated($kodeLokasi),
                    Forms\Components\TextInput::make('tahun')
                        ->label('Tahun Penempatan')
                        ->numeric()
                        ->minValue(1901)
                        ->maxValue(date('Y'))
                        ->placeholder('Semua Tahun')
                        ->live()
                        ->afterStateUpdated($kodeLokasi),
                ])
                ->visible(fn($get) => !$get('input_mode')),
            Forms\Components\TextInput::make('kode_lokasi')
                ->label('Kode Lokasi')
                ->placeholder('Masukkan kode lokasi manual')
                ->readOnly(fn($get) => !$get('input_mode'))
                ->visible(fn($get) => $get('input_mode')),
        ];

        // Form Gedung Bangunan Idle - digunakan untuk cetak dan export
        $gedungBangunanIdleForm = [
            Forms\Components\Select::make('penanggung_jawab')
                ->label('Penanggung Jawab')
                ->options(function () {
                    return \App\Models\User::pluck('nama', 'id')->toArray();
                })
                ->searchable()
                ->placeholder('Pilih Penanggung Jawab')
                ->nullable(),
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Select::make('wilayah_id')
                        ->label('Wilayah')
                        ->options(\App\Models\Lokasi\Wilayah::pluck('wilayah', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Wilayah'),
                    Forms\Components\Select::make('bidang_id')
                        ->label('Bidang')
                        ->options(\App\Models\Lokasi\Bidang::pluck('bidang', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Bidang'),
                    Forms\Components\Select::make('sub_bidang_id')
                        ->label('Sub Bidang')
                        ->options(\App\Models\Lokasi\SubBidang::pluck('sub_bidang', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Sub Bidang'),
                    Forms\Components\Select::make('unit_id')
                        ->label('Unit')
                        ->options(\App\Models\Lokasi\Unit::pluck('unit', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Unit'),
                ]),
        ];

        return [
            Actions\CreateAction::make(),
            Actions\ActionGroup::make([
                Actions\Action::make('Cetak KIB')
                    ->label('Kartu Inventaris Barang')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->form($kibForm)
                    ->action(function (array $data) {
                        $queryParams = [];
                        
                        // Add penanggung jawab parameter
                        if (!empty($data['penanggung_jawab'])) {
                            $queryParams['penanggung_jawab'] = $data['penanggung_jawab'];
                        }
                        
                        if ($data['input_mode'] && !empty($data['kode_lokasi'])) {
                            $queryParams['kode_lokasi'] = $data['kode_lokasi'];
                        } else {
                            // Add individual filter parameters
                            if (!empty($data['wilayah_id'])) {
                                $queryParams['wilayah_id'] = $data['wilayah_id'];
                            }
                            if (!empty($data['bidang_id'])) {
                                $queryParams['bidang_id'] = $data['bidang_id'];
                            }
                            if (!empty($data['sub_bidang_id'])) {
                                $queryParams['sub_bidang_id'] = $data['sub_bidang_id'];
                            }
                            if (!empty($data['unit_id'])) {
                                $queryParams['unit_id'] = $data['unit_id'];
                            }
                            if (!empty($data['tahun'])) {
                                $queryParams['tahun'] = $data['tahun'];
                            }
                        }
                        
                        $query = http_build_query($queryParams);
                        return redirect()->to(route('gedung-bangunan.cetak-kib') . '?' . $query);
                    })
                    ->modalSubmitActionLabel('Cetak'),
                Actions\Action::make('Cetak Gedung Bangunan Idle')
                    ->label('Gedung Bangunan Idle')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->form($gedungBangunanIdleForm)
                    ->action(function (array $data) {
                        $queryParams = [];
                        
                        if (!empty($data['penanggung_jawab'])) {
                            $queryParams['penanggung_jawab'] = $data['penanggung_jawab'];
                        }
                        
                        if (!empty($data['wilayah_id'])) {
                            $queryParams['wilayah_id'] = $data['wilayah_id'];
                        }
                        
                        if (!empty($data['bidang_id'])) {
                            $queryParams['bidang_id'] = $data['bidang_id'];
                        }
                        
                        if (!empty($data['sub_bidang_id'])) {
                            $queryParams['sub_bidang_id'] = $data['sub_bidang_id'];
                        }
                        
                        if (!empty($data['unit_id'])) {
                            $queryParams['unit_id'] = $data['unit_id'];
                        }
                        
                        $query = http_build_query($queryParams);
                        $url = route('gedung-bangunan.cetak-idle');
                        if (!empty($queryParams)) {
                            $url .= '?' . $query;
                        }
                        return redirect()->to($url);
                    })
                    ->modalSubmitActionLabel('Cetak'),
            ])
                ->label('Cetak')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->button(),
            Actions\ActionGroup::make([
                Actions\Action::make('KIB Export')
                    ->label('Kartu Inventaris Barang')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->form($kibForm)
                    ->action(function (array $data) {
                        $queryParams = [];
                        
                        if (!empty($data['penanggung_jawab'])) {
                            $queryParams['penanggung_jawab'] = $data['penanggung_jawab'];
                        }
                        
                        if (!empty($data['wilayah_id'])) {
                            $queryParams['wilayah_id'] = $data['wilayah_id'];
                        }
                        if (!empty($data['bidang_id'])) {
                            $queryParams['bidang_id'] = $data['bidang_id'];
                        }
                        if (!empty($data['sub_bidang_id'])) {
                            $queryParams['sub_bidang_id'] = $data['sub_bidang_id'];
                        }
                        if (!empty($data['unit_id'])) {
                            $queryParams['unit_id'] = $data['unit_id'];
                        }
                        if (!empty($data['tahun'])) {
                            $queryParams['tahun'] = $data['tahun'];
                        }
                        if (!empty($data['kode_lokasi'])) {
                            $queryParams['kode_lokasi'] = $data['kode_lokasi'];
                        }
                        
                        $query = http_build_query($queryParams);
                        return redirect()->to(route('gedung-bangunan.export-kib') . '?' . $query);
                    })
                    ->modalSubmitActionLabel('Export'),
                Actions\Action::make('Gedung Bangunan Idle Export')
                    ->label('Gedung Bangunan Idle')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->form($gedungBangunanIdleForm)
                    ->action(function (array $data) {
                        $queryParams = [];
                        
                        if (!empty($data['penanggung_jawab'])) {
                            $queryParams['penanggung_jawab'] = $data['penanggung_jawab'];
                        }
                        
                        if (!empty($data['wilayah_id'])) {
                            $queryParams['wilayah_id'] = $data['wilayah_id'];
                        }
                        
                        if (!empty($data['bidang_id'])) {
                            $queryParams['bidang_id'] = $data['bidang_id'];
                        }
                        
                        if (!empty($data['sub_bidang_id'])) {
                            $queryParams['sub_bidang_id'] = $data['sub_bidang_id'];
                        }
                        
                        if (!empty($data['unit_id'])) {
                            $queryParams['unit_id'] = $data['unit_id'];
                        }
                        
                        $query = http_build_query($queryParams);
                        $url = route('gedung-bangunan.export-idle');
                        if (!empty($queryParams)) {
                            $url .= '?' . $query;
                        }
                        return redirect()->to($url);
                    })
                    ->modalSubmitActionLabel('Export'),
            ])
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->button(),
        ];
    }
}
