<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UnitResource\Pages;
use App\Filament\Admin\Resources\UnitResource\RelationManagers\SopsRelationManager;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->label('Nama Unit'),

                Forms\Components\Select::make('direktorat_id')
                    ->relationship('direktorat', 'nama')
                    ->required()
                    ->label('Direktorat')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('nama')
                            ->required()
                            ->label('Nama Direktorat'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Unit')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('direktorat.nama')
                    ->label('Direktorat')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('sops_murni_count')
                    ->counts('sops_murni')
                    ->label('Total SOP')
                    ->badge()
                    ->color('warning')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('sop_aps_count')
                    ->counts('sop_aps')
                    ->label('Total SOP AP')
                    ->badge()
                    ->color('danger')
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('direktorat_id')
                    ->relationship('direktorat', 'nama')
                    ->label('Filter Direktorat'),
            ])
            ->recordUrl(
                fn (Unit $record) => Pages\ViewUnit::getUrl(['record' => $record->id])
            );
    }

    public static function getRelations(): array
    {
        return [
            SopsRelationManager::class,
        ];

    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'view' => Pages\ViewUnit::route('/{record}'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
