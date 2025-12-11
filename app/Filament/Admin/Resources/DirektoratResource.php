<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DirektoratResource\Pages;
use App\Filament\Admin\Resources\DirektoratResource\RelationManagers\UnitsRelationManager;
use App\Models\Direktorat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists; 
use Filament\Infolists\Infolist; 

class DirektoratResource extends Resource
{
    protected static ?string $model = Direktorat::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Direktorat';
    protected static ?string $modelLabel = 'Direktorat';
    protected static ?string $pluralModelLabel = 'Direktorat';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama Direktorat')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),
            ]);
    }

    // Tambahkan Infolist untuk tampilan View (Hanya Baca)
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Detail Direktorat')
                    ->schema([
                        Infolists\Components\TextEntry::make('nama')
                            ->label('Nama Direktorat')
                            ->weight(FontWeight::Bold)
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        
                        Infolists\Components\TextEntry::make('units_count')
                            ->state(fn ($record) => $record->units()->count() . ' Unit')
                            ->label('Jumlah Unit')
                            ->badge()
                            ->color('info'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Ini membuat baris tabel bisa diklik untuk menuju halaman View
            ->recordUrl(fn (Direktorat $record) => Pages\ViewDirektorat::getUrl([$record]))
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Direktorat')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('units_count')
                    ->counts('units')
                    ->label('Total Unit')
                    ->badge()
                    ->formatStateUsing(fn ($state) => "{$state} Unit")
                    ->color('info')
                    ->sortable(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UnitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDirektorats::route('/'),
            'create' => Pages\CreateDirektorat::route('/create'),
            'edit' => Pages\EditDirektorat::route('/{record}/edit'),
            'view' => Pages\ViewDirektorat::route('/{record}'),
        ];
    }
}