<?php

namespace App\Filament\Verifikator\Resources;

use App\Filament\Verifikator\Resources\SopResource\Pages;
use App\Models\Sop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter; 
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
class SopResource extends Resource
{
    protected static ?string $model = Sop::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Verifikasi SOP';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            // Hilangkan status 'Riwayat' dari daftar utama
            ->where('status', '!=', 'Riwayat')
            // Urutkan: Menunggu Verifikasi paling atas
            ->orderByRaw("FIELD(status, 'Menunggu Verifikasi') DESC, updated_at DESC");
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Rincian Dokumen')
                    ->schema([
                        Infolists\Components\TextEntry::make('judul')->label('Nama SOP'),
                        Infolists\Components\TextEntry::make('nomor_sk')->label('Nomor SK'),
                        Infolists\Components\TextEntry::make('unit.nama')->label('Unit'),
                        Infolists\Components\TextEntry::make('unit.direktorat.nama')->label('direktorat'),
                        Infolists\Components\TextEntry::make('jenis')->badge()->color('info'),
                        Infolists\Components\TextEntry::make('tanggal_berlaku')->label('Tanggal Pengesahan')->date('d M Y'),
                        
                        // [PERBAIKAN TAMPILAN STATUS DI VIEW DETAIL]
                        Infolists\Components\TextEntry::make('status')->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Disetujui' => 'success',
                                'Menunggu Verifikasi' => 'warning',
                                'Ditolak' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'Disetujui' => 'Aktif', // Ubah label Disetujui jadi Aktif
                                'Menunggu Verifikasi' => 'Belum Diverifikasi',
                                default => $state,
                            }),
                    ])->columns(3),

        Infolists\Components\Section::make('Preview Dokumen')
            ->schema([
                Infolists\Components\ViewEntry::make('file_path') // Nama kolom di database
                    ->label('') // Kosongkan label agar tampilan penuh
                    ->view('filament.infolists.pdf-preview') // Lokasi file blade yang akan kita buat
                    ->columnSpanFull(),
                ])
                ->collapsible(),
                        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_sk')->label('No SK'),
                Tables\Columns\TextColumn::make('judul')->label('Nama SOP')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('jenis')->badge()->color('info'),
                Tables\Columns\TextColumn::make('tanggal_berlaku')->label('Tanggal Pengesahan')->date('d M Y'),
                Tables\Columns\TextColumn::make('unit.direktorat.nama')->label('Direktorat'),
                Tables\Columns\TextColumn::make('unit.nama')->label('Unit'),
                
                // [PERBAIKAN TAMPILAN STATUS DI TABEL]
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Disetujui' => 'success', 
                        'Menunggu Verifikasi' => 'warning', 
                        'Ditolak' => 'danger', 
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Disetujui' => 'Aktif', // Ubah label Disetujui jadi Aktif
                        'Menunggu Verifikasi' => 'Belum Diverifikasi',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Menunggu Verifikasi' => 'Belum Diverifikasi',
                        'Disetujui' => 'Aktif',
                        'Ditolak' => 'Ditolak',
                    ]),
            ])
            ->recordUrl(
                fn (Sop $record) => Pages\ViewSop::getUrl(['record' => $record])
            );
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSops::route('/'),
            'view' => Pages\ViewSop::route('/{record}'),
        ];
    }
}