<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Actions;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use App\Models\KoreksiPencatatan as KoreksiPencatatanModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;

class KoreksiPencatatan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'Koreksi Pencatatan Aset';

    protected static ?string $navigationLabel = 'Koreksi Pencatatan';
    protected static ?string $navigationIcon = 'heroicon-o-pencil';
    protected static ?int $navigationSort = 900;
    protected static string $view = 'filament.pages.koreksi-pencatatan';

    public ?array $data = [];
    public $koreksiList = [];

    public function mount(): void
    {
        $this->form->fill([
            'asset_type' => null,
            'asset_id' => null,
            'rows' => [
                [
                    'kode' => null,
                    'nama' => null,
                    'jumlah' => null,
                    'harga' => null,
                    'tercatat' => null,
                    'seharusnya' => null,
                    'keterangan' => null,
                ]
            ],
        ]);

        $this->loadKoreksiList();
    }

    public function loadKoreksiList(): void
    {
        $this->koreksiList = KoreksiPencatatanModel::with('asset')
            ->latest()
            ->get()
            ->toArray();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Aset')
                    ->description('Pilih tipe aset dan ID yang akan dikoreksi')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('asset_type')
                                    ->label('Tipe Aset')
                                    ->placeholder('Pilih tipe aset...')
                                    ->options([
                                        'tanah' => 'Tanah',
                                        'gedung-bangunan' => 'Gedung & Bangunan',
                                        'peralatan-mesin' => 'Peralatan & Mesin',
                                        'jaringan' => 'Jaringan',
                                        'aset-tetap-lainnya' => 'Aset Tetap Lainnya',
                                    ])
                                    ->required('Tipe aset harus dipilih')
                                    ->searchable()
                                    ->native(false),
                                Forms\Components\TextInput::make('asset_id')
                                    ->label('Asset ID')
                                    ->placeholder('Masukkan ID aset...')
                                    ->numeric()
                                    ->required('Asset ID harus diisi')
                                    ->helperText('ID yang unik untuk aset yang akan dikoreksi'),
                            ]),
                    ]),

                Forms\Components\Section::make('Detail Koreksi')
                    ->description('Isi data koreksi untuk setiap baris aset yang salah catat')
                    ->icon('heroicon-m-pencil-square')
                    ->schema([
                        Forms\Components\Repeater::make('rows')
                            ->label('Daftar Koreksi')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('kode')
                                            ->label('Kode Barang')
                                            ->placeholder('Cth: 02.01.01')',
                                        Forms\Components\TextInput::make('nama')
                                            ->label('Nama/Jenis Barang')
                                            ->placeholder('Nama lengkap barang'),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('jumlah')
                                            ->label('Jumlah (Unit)')
                                            ->numeric()
                                            ->placeholder('0')
                                            ->helperText('Jumlah unit barang'),
                                        Forms\Components\TextInput::make('harga')
                                            ->label('Harga (Rp)')
                                            ->numeric()
                                            ->placeholder('0')
                                            ->helperText('Harga satuan dalam rupiah'),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Textarea::make('tercatat')
                                            ->label('Tercatat di KIB')
                                            ->placeholder('Data yang tercatat saat ini di KIB')
                                            ->rows(2)
                                            ->columnSpan(1),
                                        Forms\Components\Textarea::make('seharusnya')
                                            ->label('Seharusnya (Koreksi)')
                                            ->placeholder('Data yang seharusnya tercatat')
                                            ->rows(2)
                                            ->columnSpan(1),
                                    ]),
                                Forms\Components\TextInput::make('keterangan')
                                    ->label('Keterangan Alasan Koreksi')
                                    ->placeholder('Jelaskan alasan koreksi...')
                                    ->columnSpanFull()
                                    ->helperText('Isi alasan mengapa data perlu dikoreksi'),
                            ])
                            ->itemLabel(fn (array $state): ?string => ($state['nama'] ?? 'Baris baru'))
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Baris Koreksi')
                            ->reorderable(false)
                            ->collapsible()
                            ->collapsed(false),
                    ]),
            ])
            ->statePath('data')
            ->model(KoreksiPencatatanModel::class);
    }

    public function reset(): void
    {
        $this->mount();
    }

    public function create(): void
    {
        try {
            $data = $this->form->getState();

            // Validate asset type and ID are provided
            if (empty($data['asset_type']) || empty($data['asset_id'])) {
                Notification::make()
                    ->danger()
                    ->title('Data Tidak Lengkap')
                    ->body('Silakan pilih tipe aset dan masukkan asset ID.')
                    ->send();
                return;
            }

            // Calculate totals
            $rows = $data['rows'] ?? [];

            // Filter empty rows
            $filteredRows = array_filter($rows, function ($row) {
                return !empty($row['kode']) || !empty($row['nama'])
                    || !empty($row['jumlah']) || !empty($row['harga']);
            });

            if (empty($filteredRows)) {
                Notification::make()
                    ->warning()
                    ->title('Data Baris Kosong')
                    ->body('Silakan isi minimal satu baris data koreksi.')
                    ->send();
                return;
            }

            $totalJumlah = 0;
            $totalHarga = 0;

            foreach ($filteredRows as $row) {
                if (!empty($row['jumlah'])) {
                    $totalJumlah += (int)$row['jumlah'];
                }
                if (!empty($row['harga'])) {
                    $totalHarga += (float)$row['harga'];
                }
            }

            KoreksiPencatatanModel::create([
                'asset_type' => $data['asset_type'],
                'asset_id' => $data['asset_id'],
                'user_id' => Auth::id(),
                'data' => array_values($filteredRows),
                'total_jumlah' => $totalJumlah,
                'total_harga' => $totalHarga,
            ]);

            Notification::make()
                ->success()
                ->title('Koreksi Pencatatan Berhasil')
                ->body('Data koreksi pencatatan aset telah disimpan dan siap untuk ditinjau.')
                ->send();

            // Reset form to initial state
            $this->reset();

            // Refresh the list
            $this->loadKoreksiList();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Terjadi Kesalahan')
                ->body($e->getMessage())
                ->send();
        }
    }

}
