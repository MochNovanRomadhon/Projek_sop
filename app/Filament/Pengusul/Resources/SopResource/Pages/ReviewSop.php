<?php

namespace App\Filament\Pengusul\Resources\SopResource\Pages;

use App\Filament\Pengusul\Resources\SopResource;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class ReviewSop extends EditRecord
{
    protected static string $resource = SopResource::class;

    protected static ?string $title = 'Review Tahunan SOP';

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Simpan Keputusan')
            ->icon('heroicon-o-check');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Konfirmasi Review Tahunan')
                    ->description('SOP ini telah memasuki masa review tahunan. Silakan tentukan statusnya.')
                    ->schema([
                        Forms\Components\Radio::make('keputusan_review')
                            ->label('Apakah SOP ini masih relevan?')
                            ->options([
                                'relevan' => 'Masih Relevan (Tidak ada perubahan)',
                                'tidak' => 'Tidak Relevan (Perlu direvisi/diperbarui)',
                            ])
                            ->default('relevan')
                            ->required()
                            ->columnSpanFull()
                            ->live(),
                    ]),

                Forms\Components\Section::make('Pembaruan Data')
                    ->visible(fn (Forms\Get $get) => $get('keputusan_review') === 'tidak')
                    ->schema([
                        Forms\Components\TextInput::make('judul')->required()->label('Nama SOP Baru'),
                        Forms\Components\TextInput::make('nomor_sk')->required()->label('Nomor SK Baru'),
                        
                        // User input tanggal pengesahan baru
                        Forms\Components\DatePicker::make('tanggal_berlaku')
                            ->required()
                            ->label('Tanggal Pengesahan Baru'),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('Upload File Baru')
                            ->directory('sops')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->columnSpanFull(),
                        
                        Forms\Components\Hidden::make('unit_id')->default(auth()->user()->unit_id),
                        Forms\Components\Hidden::make('status'), 
                    ])->columns(2),
            ]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $keputusan = $data['keputusan_review'] ?? 'relevan';
        unset($data['keputusan_review']);

        if ($keputusan === 'relevan') {
            // [LOGIKA: MASIH RELEVAN]
            // Update tanggal_verifikasi ke HARI INI agar dianggap sudah direview
            $record->update([
                'status' => 'Disetujui',
                'tanggal_verifikasi' => now(), 
            ]);
            
            SopResource::sendNotificationToVerifikator($record, 'relevan');
            
            Notification::make()
                ->title('Review Selesai')
                ->body('Status kembali Aktif. Perhitungan tahunan dilanjutkan.')
                ->success()
                ->send();

        } else {
            // [LOGIKA: TIDAK RELEVAN]
            $oldSop = $record->replicate();
            $oldSop->judul = $record->judul . ' (Arsip ' . now()->year . ')';
            $oldSop->status = 'Riwayat';
            $oldSop->created_at = $record->created_at;
            $oldSop->save();

            $record->update($data); 
            $record->update([
                'status' => 'Menunggu Verifikasi',
                'catatan_revisi' => null,
                'tanggal_verifikasi' => null, 
            ]);

            SopResource::sendNotificationToVerifikator($record, 'baru');
            
            Notification::make()
                ->title('Pembaruan Diajukan')
                ->body('SOP versi baru telah dikirim ke Verifikator.')
                ->success()
                ->send();
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}