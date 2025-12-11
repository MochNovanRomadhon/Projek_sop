<?php

namespace App\Console\Commands;

use App\Models\Sop;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckSopReview extends Command
{
    protected $signature = 'sop:check-review';
    protected $description = 'Cek SOP yang sudah 1 tahun dan ubah status jadi Review';

    public function handle()
    {
        // Cari SOP Aktif yang tanggal verifikasinya sudah lewat 1 tahun (atau pas 1 tahun)
        // Dan belum status 'Review'
        $sops = Sop::where('status', 'Disetujui') // Status Aktif
            ->whereDate('tanggal_verifikasi', '<=', Carbon::now()->subYear())
            ->get();

        foreach ($sops as $sop) {
            $sop->update(['status' => 'Review']);
            $this->info("SOP {$sop->judul} masuk masa Review.");
        }
    }
}