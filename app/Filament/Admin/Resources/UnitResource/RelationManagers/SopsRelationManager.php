<?php

namespace App\Filament\Admin\Resources\UnitResource\RelationManagers;

use App\Filament\Admin\Resources\SopResource; // Import Resource Admin
use App\Models\Sop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SopsRelationManager extends RelationManager
{
    protected static string $relationship = 'sops';

    protected static ?string $title = 'Daftar SOP Unit (Aktif)';

    protected static ?string $icon = 'heroicon-o-document-text';

    public function form(Form $form): Form
    {
        return SopResource::form($form);
    }

    public function table(Table $table): Table
    {
        return $table
            // 1. FILTER: Admin HANYA menampilkan status 'Disetujui' (Aktif)
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Disetujui'))
            
            // 2. KOLOM (Disamakan strukturnya dengan Verifikator)
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->label('Nama SOP')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('nomor_sk')
                    ->label('No. SK')
                    ->searchable(),

                Tables\Columns\TextColumn::make('jenis')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('tanggal_berlaku')
                    ->date('d M Y')
                    ->label('Tgl Berlaku'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color('success') // Pasti sukses karena difilter Disetujui
                    ->formatStateUsing(fn () => 'Aktif'),
            ])
            
            // 3. HEADER ACTIONS (Tombol Riwayat disamakan gayanya)
            ->headerActions([
                // Tombol menuju Halaman Global Riwayat SOP
                Tables\Actions\Action::make('riwayat')
                    ->label('Lihat Riwayat SOP')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    // Mengarah ke halaman ListRiwayatSops yang sudah dibuat di SopResource
                    ->url(fn () => SopResource::getUrl('riwayat')), 

                Tables\Actions\CreateAction::make()
                    ->visible(fn () => auth()->user()->hasRole(['admin', 'pengusul'])),
            ])
            
            // 4. AKSI BARIS (Admin actions: View, Edit, Download)
            ->actions([
                
                Tables\Actions\Action::make('download')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Sop $record) => asset('storage/' . $record->file_path))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

            
                ]),
            ]);
    }
}