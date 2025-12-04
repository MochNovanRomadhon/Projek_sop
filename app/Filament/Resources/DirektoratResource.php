<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DirektoratResource\Pages;
use App\Filament\Resources\DirektoratResource\RelationManagers;
use App\Models\Direktorat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\FontWeight;

class DirektoratResource extends Resource
{
    protected static ?string $model = Direktorat::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Direktorat (Folder)';

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('nama') // SESUAI DATABASE
                ->required()
                ->maxLength(255)
                ->label('Nama Direktorat'),
                
            Forms\Components\TextInput::make('kode') // Tambahan sesuai migrasi
                ->label('Kode Direktorat'),
        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Stack::make([
                TextColumn::make('icon_folder')
                    ->default('📂') 
                    ->extraAttributes(['class' => 'text-6xl text-center pb-2']), 

                TextColumn::make('nama') // SESUAI DATABASE
                    ->weight(FontWeight::Bold)
                    ->alignCenter()
                    ->searchable(),

                TextColumn::make('units_count')
                    ->counts('units')
                    ->formatStateUsing(fn ($state) => "$state Unit")
                    ->color('gray')
                    ->alignCenter()
                    ->size(TextColumn\TextColumnSize::ExtraSmall),
            ])->space(2),
        ])
            ->contentGrid([
                'md' => 2,
                'xl' => 4, // 4 Kolom folder per baris
            ])
            ->actions([
                // Saat diklik, masuk ke View Page (untuk melihat isinya/Unit)
                Tables\Actions\ViewAction::make()
                    ->label('Buka Folder')
                    ->button(),
                Tables\Actions\EditAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        // Pastikan Relation Manager ini terdaftar!
        return [
            RelationManagers\UnitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDirektorats::route('/'),
            'create' => Pages\CreateDirektorat::route('/create'),
            // Kita butuh page View untuk menampilkan Relation Manager
            'view' => Pages\ViewDirektorat::route('/{record}'), 
            'edit' => Pages\EditDirektorat::route('/{record}/edit'),
        ];
    }
}