<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SopResource\Pages;
use App\Models\Sop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Infolists;
use Filament\Infolists\Infolist;


class SopResource extends Resource
{
    protected static ?string $model = Sop::class;

    protected static ?string $navigationLabel = 'Dokumen SOP';

    // Diset false agar tidak muncul di sidebar utama (akses lewat UnitResource)
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Dokumen SOP')
                    ->description('Lengkapi detail dokumen SOP di bawah ini.')
                    ->schema([
                        Forms\Components\TextInput::make('judul')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama / Judul SOP')
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('jenis')
                            ->options([
                                'SOP' => 'SOP Unit',
                                'SOP AP' => 'SOP Administrasi Pemerintahan',
                            ])
                            ->default('SOP')
                            ->required()
                            ->label('Jenis Dokumen'),

                        Forms\Components\TextInput::make('nomor_sk')
                            ->label('Nomor SK')
                            ->placeholder('Masukkan Nomor SK jika ada'),

                        Forms\Components\DatePicker::make('tanggal_berlaku')
                            ->label('Tanggal Berlaku')
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->default(now()),

                        Forms\Components\Select::make('unit_id')
                            ->relationship('unit', 'nama')
                            ->label('Unit Kerja')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => auth()->user()->unit_id)
                            ->disabled(fn () => !auth()->user()->hasRole(['admin', 'verifikator']))
                            ->dehydrated(),
                    ])->columns(2),

                Forms\Components\Section::make('File Dokumen')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('File SOP (PDF)')
                            ->directory('sops')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->required()
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Catatan Revisi')
                    ->schema([
                        Forms\Components\Placeholder::make('alasan')
                            ->content(fn ($record) => $record?->catatan_revisi)
                            ->label('Alasan Penolakan:')
                            ->extraAttributes([
                                'class' => 'text-danger-600 font-bold bg-red-50 p-3 rounded'
                            ]),
                    ])
                    ->visible(fn ($record) => $record?->status === 'Ditolak'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Detail SOP')
                    ->schema([
                        Infolists\Components\TextEntry::make('judul')->label('Nama SOP'),
                        Infolists\Components\TextEntry::make('nomor_sk')->label('Nomor SK'),
                        Infolists\Components\TextEntry::make('jenis')->badge()->color('info'),
                        Infolists\Components\TextEntry::make('tanggal_berlaku')->date('d F Y'),
                        Infolists\Components\TextEntry::make('unit.nama')->label('Unit Kerja'),
                        
                        // LOGIKA STATUS VIEW (Infolist)
                        Infolists\Components\TextEntry::make('status')->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Disetujui' => 'success', 
                                'Menunggu Verifikasi' => 'warning', 
                                'Ditolak' => 'danger', 
                                'Review' => 'info', 
                                'Riwayat' => 'gray', 
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'Disetujui' => 'Aktif',
                                'Menunggu Verifikasi' => 'Sedang Diproses',
                                'Review' => 'Butuh Review Tahunan',
                                'Riwayat' => 'Riwayat / Arsip',
                                default => $state,
                            }),

                        Infolists\Components\TextEntry::make('catatan_revisi')
                            ->label('Catatan Revisi')
                            ->visible(fn ($record) => $record->status === 'Ditolak')
                            ->color('danger'),

                        Infolists\Components\TextEntry::make('file_path')
                            ->label('File Dokumen')
                            ->formatStateUsing(fn () => 'Download PDF')
                            ->url(fn ($record) => asset('storage/' . $record->file_path))
                            ->openUrlInNewTab()
                            ->color('primary'),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_sk')->label('No SK'),
                
                Tables\Columns\TextColumn::make('judul')
                    ->label('Nama SOP')
                    ->searchable()
                    ->sortable()
                    // Description Unit tetap ada agar Admin tahu SOP milik siapa
                    ->description(fn (Sop $record) => $record->unit->nama ?? '-')
                    ->wrap()
                    ->weight('bold'), // Disamakan dengan Pengusul

                Tables\Columns\TextColumn::make('jenis')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('tanggal_berlaku')
                    ->date('d M Y') // Format disamakan
                    ->sortable(),

                // === LOGIKA STATUS TABEL (Sama Persis dengan Pengusul) ===
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->color(fn (string $state): string => match ($state) {
                        'Disetujui' => 'success', 
                        'Menunggu Verifikasi' => 'warning', 
                        'Ditolak' => 'danger', 
                        'Review' => 'info', 
                        'Riwayat' => 'gray', 
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Disetujui' => 'Aktif',
                        'Menunggu Verifikasi' => 'Proses Verifikasi',
                        'Ditolak' => 'Ditolak',
                        'Review' => 'Review Tahunan',
                        'Riwayat' => 'Riwayat',
                        default => $state,
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Review' => 'heroicon-m-arrow-path',
                        'Disetujui' => 'heroicon-o-check-circle',
                        'Ditolak' => 'heroicon-o-x-circle',
                        'Menunggu Verifikasi' => 'heroicon-o-clock',
                        default => 'heroicon-o-archive-box',
                    }),
                // =========================================================

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Tgl Update')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Draft' => 'Draft',
                        'Menunggu Verifikasi' => 'Proses / Pending',
                        'Disetujui' => 'Aktif',
                        'Ditolak' => 'Ditolak',
                        'Review' => 'Review',
                        'Riwayat' => 'Riwayat',
                    ]),

                SelectFilter::make('unit_id')
                    ->relationship('unit', 'nama')
                    ->label('Unit Kerja')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // ACTION: APPROVE
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->button()
                    ->size('sm')
                    ->color('success')
                    ->visible(fn (Sop $record) => 
                        $record->status === 'Menunggu Verifikasi' 
                        && (auth()->user()->hasRole('verifikator') || auth()->user()->hasRole('admin'))
                    )
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_berlaku')->required()->default(now()),
                        Forms\Components\TextInput::make('nomor_sk')->required(),
                    ])
                    ->action(fn (Sop $record, array $data) => 
                        $record->update([
                            'status' => 'Disetujui',
                            'tanggal_berlaku' => $data['tanggal_berlaku'],
                            'nomor_sk' => $data['nomor_sk']
                        ])
                    ),

                // ACTION: REJECT
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->button()
                    ->size('sm')
                    ->color('danger')
                    ->visible(fn (Sop $record) => 
                        $record->status === 'Menunggu Verifikasi' 
                        && (auth()->user()->hasRole('verifikator') || auth()->user()->hasRole('admin'))
                    )
                    ->form([
                        Forms\Components\Textarea::make('catatan_revisi')->required()->label('Alasan Penolakan'),
                    ])
                    ->action(fn (Sop $record, array $data) => 
                        $record->update([
                            'status' => 'Ditolak',
                            'catatan_revisi' => $data['catatan_revisi']
                        ])
                    ),

                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('Unduh')
                    ->url(fn (Sop $record) => asset('storage/' . $record->file_path))
                    ->openUrlInNewTab(),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSops::route('/'),
            // [PENTING] Route 'riwayat' diletakkan sebelum 'edit' dan 'view'
            // agar tidak dianggap sebagai ID (/{record})
            'riwayat' => Pages\ListRiwayatSops::route('/riwayat'),
            
            'create' => Pages\CreateSop::route('/create'),
            'view' => Pages\ViewSop::route('/{record}'),
        ];
    }
}