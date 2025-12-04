<?php

namespace App\Filament\Resources\DirektoratResource\RelationManagers;

use App\Filament\Resources\SopResource;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\FontWeight;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';
    protected static ?string $title = 'Unit Kerja (Sub-Folder)';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

public function table(Table $table): Table
{
    return $table
        ->recordTitleAttribute('nama') // SESUAI DATABASE
        ->columns([
            Stack::make([
                TextColumn::make('icon')
                    ->default('📁') 
                    ->extraAttributes(['class' => 'text-5xl text-center pb-2']), 
                
                TextColumn::make('nama') // SESUAI DATABASE
                    ->weight(FontWeight::Bold)
                    ->alignCenter(),

                TextColumn::make('sops_count')
                    ->counts('sops')
                    ->formatStateUsing(fn ($state) => "$state SOP")
                    ->alignCenter()
                    ->color('gray'),
            ])
        ])
            ->contentGrid([
                'md' => 3,
                'xl' => 4,
            ])
            ->actions([
                // AKSI UTAMA: Link ke Halaman SOP Resource dengan Filter Unit ID
                Tables\Actions\Action::make('open_sops')
                    ->label('Lihat SOP')
                    ->icon('heroicon-m-folder-open')
                    ->button()
                    ->url(fn (Unit $record): string => 
                        SopResource::getUrl('index', [
                            'tableFilters' => [
                                'unit_id' => [ 
                                    'value' => $record->id,
                                ],
                            ],
                        ])
                    ),
            ]);
    }
}