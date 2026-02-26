<?php

namespace App\Filament\Resources\TanahResource\Pages;

use Filament\Forms;
use Filament\Actions;
use App\Models\Lokasi\Bidang;
use App\Models\Lokasi\Wilayah;
use App\Models\Lokasi\SubBidang;
use App\Models\Aset\SubSubKelompok;
use App\Filament\Resources\TanahResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTanah extends ListRecords
{
    protected static string $resource = TanahResource::class;

    protected function getHeaderActions(): array
    {
        $wilayahList = Wilayah::pluck('wilayah', 'id')->toArray();
        $bidangList = Bidang::pluck('bidang', 'id')->toArray();
        $subBidangList = SubBidang::pluck('sub_bidang', 'id')->toArray();
        $subSubKelompokList = SubSubKelompok::where('id_sub_kelompok', 'like', '02.%')->pluck('sub_sub_kelompok', 'id')->toArray();

        // Form KIB - digunakan untuk cetak dan export
        $kibForm = [
            Forms\Components\Select::make('wilayah_id')
                ->label('Wilayah')
                ->options($wilayahList)
                ->searchable()
                ->preload()
                ->placeholder('Semua Wilayah'),
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\Select::make('bidang_id')
                        ->label('Bidang')
                        ->options($bidangList)
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Bidang'),
                    Forms\Components\Select::make('sub_bidang_id')
                        ->label('Sub Bidang')
                        ->options($subBidangList)
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Sub Bidang'),
                    Forms\Components\Select::make('unit_id')
                        ->label('Unit')
                        ->options(\App\Models\Lokasi\Unit::pluck('unit', 'id'))
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Unit')
                        ->nullable(),
                    Forms\Components\Select::make('sub_sub_kelompok_id')
                        ->label('Sub Sub Kelompok')
                        ->options($subSubKelompokList)
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Sub Sub Kelompok'),
                ]),
            Forms\Components\Select::make('penanggung_jawab')
                ->label('Penanggung Jawab')
                ->options(function () {
                    return \App\Models\User::pluck('nama', 'id')->toArray();
                })
                ->searchable()
                ->placeholder('Pilih Penanggung Jawab')
                ->nullable(),
        ];

        // Form Tanah Idle - digunakan untuk cetak dan export
        $tanahIdleForm = [
            Forms\Components\Select::make('wilayah_id')
                ->label('Wilayah')
                ->options($wilayahList)
                ->searchable()
                ->preload()
                ->placeholder('Semua Wilayah'),
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\Select::make('bidang_id')
                        ->label('Bidang')
                        ->options($bidangList)
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Bidang'),
                    Forms\Components\Select::make('sub_bidang_id')
                        ->label('Sub Bidang')
                        ->options($subBidangList)
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Sub Bidang'),
                    Forms\Components\Select::make('sub_sub_kelompok_id')
                        ->label('Sub Sub Kelompok')
                        ->options($subSubKelompokList)
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Sub Sub Kelompok'),
                ]),
            Forms\Components\Select::make('penanggung_jawab')
                ->label('Penanggung Jawab')
                ->options(function () {
                    return \App\Models\User::pluck('nama', 'id')->toArray();
                })
                ->searchable()
                ->placeholder('Pilih Penanggung Jawab')
                ->nullable(),
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
                        
                        if (!empty($data['sub_sub_kelompok_id'])) {
                            $queryParams['sub_sub_kelompok_id'] = $data['sub_sub_kelompok_id'];
                        }
                        
                        if (!empty($data['penanggung_jawab'])) {
                            $queryParams['penanggung_jawab'] = $data['penanggung_jawab'];
                        }
                        
                        $query = http_build_query($queryParams);
                        $url = route('tanah.cetak-kib');
                        if (!empty($queryParams)) {
                            $url .= '?' . $query;
                        }
                        return redirect()->to($url);
                    })
                    ->modalSubmitActionLabel('Cetak'),
                Actions\Action::make('Cetak Tanah Idle')
                    ->label('Tanah Idle')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->form($tanahIdleForm)
                    ->action(function (array $data) {
                        $queryParams = [];
                        
                        if (!empty($data['wilayah_id'])) {
                            $queryParams['wilayah_id'] = $data['wilayah_id'];
                        }
                        
                        if (!empty($data['bidang_id'])) {
                            $queryParams['bidang_id'] = $data['bidang_id'];
                        }
                        
                        if (!empty($data['sub_bidang_id'])) {
                            $queryParams['sub_bidang_id'] = $data['sub_bidang_id'];
                        }
                        
                        if (!empty($data['sub_sub_kelompok_id'])) {
                            $queryParams['sub_sub_kelompok_id'] = $data['sub_sub_kelompok_id'];
                        }
                        
                        if (!empty($data['penanggung_jawab'])) {
                            $queryParams['penanggung_jawab'] = $data['penanggung_jawab'];
                        }
                        
                        $query = http_build_query($queryParams);
                        $url = route('tanah.cetak-idle');
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
                        
                        if (!empty($data['sub_sub_kelompok_id'])) {
                            $queryParams['sub_sub_kelompok_id'] = $data['sub_sub_kelompok_id'];
                        }
                        
                        $query = http_build_query($queryParams);
                        $url = route('tanah.export-kib');
                        if (!empty($queryParams)) {
                            $url .= '?' . $query;
                        }
                        return redirect()->to($url);
                    })
                    ->modalSubmitActionLabel('Export'),
                Actions\Action::make('Tanah Idle Export')
                    ->label('Tanah Idle')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->form($tanahIdleForm)
                    ->action(function (array $data) {
                        $queryParams = [];
                        
                        if (!empty($data['wilayah_id'])) {
                            $queryParams['wilayah_id'] = $data['wilayah_id'];
                        }
                        
                        if (!empty($data['bidang_id'])) {
                            $queryParams['bidang_id'] = $data['bidang_id'];
                        }
                        
                        if (!empty($data['sub_bidang_id'])) {
                            $queryParams['sub_bidang_id'] = $data['sub_bidang_id'];
                        }
                        
                        if (!empty($data['sub_sub_kelompok_id'])) {
                            $queryParams['sub_sub_kelompok_id'] = $data['sub_sub_kelompok_id'];
                        }
                        
                        $query = http_build_query($queryParams);
                        $url = route('tanah.export-idle');
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

    public function getTableQuery(): Builder|null
    {
        $query = parent::getTableQuery();
        $wilayahId = request()->get('filter_wilayah_id');
        if ($wilayahId) {
            $query->where('wilayah_id', $wilayahId);
        }
        return $query;
    }

    protected function getTableFilters(): array
    {
        $wilayahList = Wilayah::pluck('wilayah', 'id')->toArray();
        return [
            Forms\Components\SelectFilter::make('filter_wilayah_id')
                ->label('Wilayah')
                ->options($wilayahList)
                ->query(function ($query, $value) {
                    if ($value) {
                        $query->where('wilayah_id', $value);
                    }
                }),
        ];
    }
}
