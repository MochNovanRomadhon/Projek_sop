<?php

namespace App\Filament\Pengusul\Resources\SopResource\Pages;

use App\Filament\Pengusul\Resources\SopResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class ListRiwayatSops extends ListRecords
{
    protected static string $resource = SopResource::class;

    protected static ?string $title = 'Riwayat SOP';

    protected static ?string $breadcrumb = 'Riwayat';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->where('unit_id', auth()->user()->unit_id)
                ->where('status', 'Riwayat')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nomor_sk')->label('No SK'),
                Tables\Columns\TextColumn::make('judul')->label('Nama SOP')->searchable(),
                Tables\Columns\TextColumn::make('jenis')->badge()->color('info'),
                Tables\Columns\TextColumn::make('tanggal_berlaku')->date('d M Y'),
                Tables\Columns\TextColumn::make('updated_at')->label('Diarsipkan')->date('d M Y'),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => asset('storage/' . $record->file_path))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]); 
    }
}