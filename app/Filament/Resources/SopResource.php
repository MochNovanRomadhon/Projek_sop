<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SopResource\Pages;
use App\Models\Sop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class SopResource extends Resource
{
    protected static ?string $model = Sop::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make('judul') // SESUAI DATABASE
                        ->required()
                        ->label('Judul SOP'),
                    
                    Forms\Components\TextInput::make('nomor_sk') // Tambahan sesuai migrasi
                        ->label('Nomor SK'),

                    Forms\Components\Select::make('unit_id')
                        ->relationship('unit', 'nama') // Relasi ke 'nama' unit
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\FileUpload::make('file_path')
                        ->label('File SOP (PDF)')
                        ->directory('sops')
                        ->acceptedFileTypes(['application/pdf']),
                        
                    // Field jenis (SOP / SOP AP) sesuai migrasi
                    Forms\Components\Select::make('jenis')
                        ->options([
                            'SOP' => 'SOP',
                            'SOP AP' => 'SOP AP',
                        ])
                        ->default('SOP')
                        ->required(),
                ])
        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('judul') // SESUAI DATABASE
                ->searchable()
                ->description(fn (Sop $record) => $record->unit->nama ?? '-') // Pakai 'nama'
                ->label('Judul SOP'),
            
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Draft' => 'gray',
                    'Menunggu Verifikasi' => 'warning',
                    'Disetujui' => 'success',
                    'Ditolak' => 'danger',
                    'Arsip' => 'info',
                    default => 'gray',
                }),
            
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime(),
        ])
        ->filters([
            SelectFilter::make('unit_id')
                ->relationship('unit', 'nama') // Pakai 'nama'
                ->label('Unit Kerja'),
        ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    // ... getPages dsb biarkan default
    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSops::route('/'),
            'create' => Pages\CreateSop::route('/create'),
            'edit' => Pages\EditSop::route('/{record}/edit'),
        ];
    }
}