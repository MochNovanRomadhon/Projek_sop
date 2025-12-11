<?php

namespace App\Filament\Admin\Resources\DirektoratResource\RelationManagers;

use App\Filament\Admin\Resources\UnitResource; 
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    protected static ?string $title = 'Daftar Unit Kerja';

    protected static ?string $icon = 'heroicon-o-rectangle-group';

    // Form ini yang akan muncul di Popup saat klik Edit
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama Unit')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // [PENTING] Baris tabel bisa diklik untuk menuju halaman View Unit (List SOP)
            ->recordUrl(fn ($record) => UnitResource::getUrl('view', ['record' => $record]))
            
            ->recordTitleAttribute('nama')
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Unit')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sops_count')
                    ->counts('sops')
                    ->label('Total SOP')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Unit')
                    ->modalHeading('Tambah Unit Baru')
                    ->createAnother(false),
            ])
            ->actions([
                // Action 'lihat' SUDAH DIHAPUS karena digantikan recordUrl diatas

                // 1. Tombol UBAH (Hanya Popup untuk ganti nama)
                Tables\Actions\EditAction::make()
                    ->label('Ubah Nama')
                    ->color('warning')
                    ->modalHeading('Ubah Nama Unit')
                    ->modalWidth('md'), 
                
                // 2. Tombol HAPUS
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}