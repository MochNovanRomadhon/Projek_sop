<?php

namespace App\Filament\Pengusul\Resources;

use App\Filament\Pengusul\Resources\SopResource\Pages;
use App\Models\Sop;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotifAction; 
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class SopResource extends Resource
{
    protected static ?string $model = Sop::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationLabel = 'Daftar SOP';
    protected static ?string $title = 'Daftar SOP'; 

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('unit_id', auth()->user()->unit_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Form Pengajuan SOP')
                    ->schema([
                        Forms\Components\TextInput::make('judul')->label('Nama SOP')->required()->maxLength(255),
                        Forms\Components\TextInput::make('nomor_sk')->label('Nomor SK')->required(),
                        Forms\Components\Select::make('jenis')
                            ->options(['SOP' => 'SOP Unit', 'SOP AP' => 'SOP AP'])->default('SOP')->required(),
                        
                        Forms\Components\DatePicker::make('tanggal_berlaku')
                            ->label('Tanggal Pengesahan')
                            ->helperText('Tanggal ditandatanganinya SOP.')
                            ->required()
                            ->native(false)
                            ->displayFormat('d F Y'),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('Upload File PDF')->directory('sops')->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)->required()->columnSpanFull(),
                        
                        Forms\Components\Hidden::make('unit_id')->default(auth()->user()->unit_id),
                        Forms\Components\Hidden::make('status')->default('Menunggu Verifikasi'), 

                        Forms\Components\Placeholder::make('pesan_revisi')
                            ->label('Catatan Revisi Verifikator:')
                            ->content(fn ($record) => $record?->catatan_revisi)
                            ->visible(fn ($record) => $record?->status === 'Ditolak')
                            ->extraAttributes(['class' => 'bg-red-50 text-red-600 p-3 rounded font-bold border border-red-200']),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul')->label('Nama SOP')->searchable()->wrap()->weight('bold')->description(fn (Sop $record) => $record->nomor_sk),
                Tables\Columns\TextColumn::make('jenis')->badge()->color('info'),
                
                Tables\Columns\TextColumn::make('tanggal_berlaku')
                    ->label('Tgl Pengesahan')
                    ->date('d M Y')
                    ->sortable(),

                // [LOGIKA WARNING]
                Tables\Columns\TextColumn::make('expired_at')
                    ->label('Berlaku Sampai')
                    ->state(fn ($record) => Carbon::parse($record->tanggal_berlaku)->addYears(3))
                    ->date('d M Y')
                    ->description(function (Sop $record) {
                        if ($record->status !== 'Disetujui') return null;

                        // Gunakan Helper yang sama agar konsisten dengan Tombol
                        if (self::needsReview($record)) {
                            $tglPengesahan = Carbon::parse($record->tanggal_berlaku);
                            $now = Carbon::now();
                            // Cari tahu review tahun ke berapa
                            for ($i = 1; $i < 3; $i++) {
                                $reviewDate = $tglPengesahan->copy()->addYears($i)->endOfDay();
                                if ($now->diffInDays($reviewDate, false) > -60) { // Masuk range
                                    $daysLeft = $now->diffInDays($reviewDate, false);
                                    if ($daysLeft < 0) return "⚠️ Terlewat! Segera lakukan review.";
                                    return "⚠️ Review Tahunan: " . ($daysLeft < 1 ? "Hari Ini!" : "{$daysLeft} hari lagi");
                                }
                            }
                        }
                        return null;
                    })
                    ->color(fn ($record) => self::needsReview($record) ? 'danger' : 'gray'),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Disetujui' => 'success', 
                        'Menunggu Verifikasi' => 'warning', 
                        'Ditolak' => 'danger', 
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Disetujui' => 'Aktif',
                        'Menunggu Verifikasi' => 'Proses Verifikasi',
                        'Ditolak' => 'Ditolak (Perlu Revisi)',
                        'Review' => 'Review Tahunan',
                        'Riwayat' => 'Riwayat',
                        default => $state,
                    }),
            ])
            ->actions([
                // 1. TOMBOL REVIEW (Menggunakan Logic Baru)
                Tables\Actions\Action::make('review')
                    ->label('Lakukan Review')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->button()
                    ->visible(fn (Sop $record) => self::needsReview($record))
                    ->url(fn (Sop $record) => Pages\ReviewSop::getUrl(['record' => $record])),

                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\Action::make('revisi')
                    ->label('Perbaiki')
                    ->icon('heroicon-o-pencil-square')
                    ->button()->size('sm')->color('danger')
                    ->visible(fn (Sop $record) => $record->status === 'Ditolak')
                    ->url(fn (Sop $record) => Pages\RevisiSop::getUrl(['record' => $record])),

                Tables\Actions\Action::make('edit_custom')
                    ->label('Ajukan Perubahan')
                    ->color('info')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (Sop $record) => Pages\EditSop::getUrl(['record' => $record]))
                    ->visible(fn (Sop $record) => in_array($record->status, ['Draft', 'Disetujui']) && !self::needsReview($record)),

                Tables\Actions\Action::make('submit')
                    ->label('Ajukan')
                    ->icon('heroicon-o-paper-airplane')
                    ->button()->size('sm')->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (Sop $record) => $record->status === 'Draft')
                    ->action(function (Sop $record) {
                        $record->update(['status' => 'Menunggu Verifikasi']);
                        self::sendNotificationToVerifikator($record, 'submit');
                        Notification::make()->title('SOP Diajukan')->success()->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Sop $record) => in_array($record->status, ['Draft', 'Ditolak'])),
            ])
            ->defaultSort('updated_at', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('riwayat')
                    ->label('Lihat Riwayat SOP')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->url(fn () => SopResource::getUrl('riwayat')),
            ]);
    }

    // --- HELPER LOGIC (YANG DIPERBAIKI) ---
    public static function needsReview(Sop $record): bool
    {
        if ($record->status !== 'Disetujui') {
            return false;
        }

        $tglPengesahan = Carbon::parse($record->tanggal_berlaku);
        // Kita gunakan tanggal_verifikasi sebagai penanda "Kapan Terakhir Direview"
        $lastReview = $record->tanggal_verifikasi ? Carbon::parse($record->tanggal_verifikasi) : $tglPengesahan;
        
        $now = Carbon::now();

        // Cek Tahun ke-1 dan Tahun ke-2
        for ($i = 1; $i < 3; $i++) {
            $reviewDate = $tglPengesahan->copy()->addYears($i)->endOfDay();
            $windowStart = $reviewDate->copy()->subDays(30)->startOfDay();

            // Apakah HARI INI masuk periode review?
            // (Dari H-30 sampai H+60 untuk toleransi keterlambatan)
            $isInReviewPeriod = $now->between($windowStart, $reviewDate) || 
                                ($now->greaterThan($reviewDate) && $now->diffInDays($reviewDate) < 60);

            if ($isInReviewPeriod) {
                // KUNCI PERBAIKAN:
                // Cek apakah 'lastReview' (tanggal verifikasi terakhir) lebih lama dari 'windowStart'?
                // Jika Last Review < Window Start, berarti belum direview pada periode ini.
                if ($lastReview->lessThan($windowStart)) {
                    return true; // Munculkan Tombol!
                }
            }
        }

        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Detail SOP')->schema([
                Infolists\Components\TextEntry::make('judul'),
                Infolists\Components\TextEntry::make('nomor_sk'),
                Infolists\Components\TextEntry::make('tanggal_berlaku')->label('Tanggal Pengesahan')->date('d M Y'),
                Infolists\Components\TextEntry::make('expired_at')->label('Berlaku Sampai')
                    ->state(fn ($record) => Carbon::parse($record->tanggal_berlaku)->addYears(3)->format('d M Y'))
                    ->badge()->color('danger'),
                Infolists\Components\TextEntry::make('status')->badge()->color('success'),
                Infolists\Components\TextEntry::make('file_path')->label('File')->url(fn ($record) => asset('storage/' . $record->file_path))->openUrlInNewTab()->color('primary'),
            ])->columns(2)
        ]);
    }

    public static function sendNotificationToVerifikator($record, $type)
    {
        $verifikators = User::whereHas('roles', fn ($q) => $q->where('name', 'verifikator'))->get();
        if ($verifikators->count() > 0) {
            $msg = match($type) {
                'relevan' => "Review Tahunan: SOP {$record->judul} Masih Relevan",
                'revisi' => "Perubahan SOP: {$record->judul}",
                default => "SOP Baru: {$record->judul}",
            };

            $notification = Notification::make()->title($msg)->warning()->body("Oleh: " . auth()->user()->name)
                ->actions([NotifAction::make('view')->url("/verifikator/sops/{$record->id}")->markAsRead()]);
            
            $notification->sendToDatabase($verifikators);
            $notification->broadcast($verifikators);
        }
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSops::route('/'),
            'create' => Pages\CreateSop::route('/create'),
            'edit' => Pages\EditSop::route('/{record}/edit'),
            'revisi' => Pages\RevisiSop::route('/{record}/revisi'),
            'riwayat' => Pages\ListRiwayatSops::route('/riwayat'),
            'view' => Pages\ViewSop::route('/{record}'), 
            'review' => Pages\ReviewSop::route('/{record}/review'),
        ];
    }
}