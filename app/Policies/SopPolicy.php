<?php

namespace App\Policies;

use App\Models\Sop;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SopPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Sop $sop): bool
    {
        // Admin & Verifikator boleh MELIHAT semua
        if ($user->hasRole(['admin', 'verifikator'])) return true;
        
        // Pengusul/User lain hanya boleh lihat milik unitnya sendiri
        return $user->unit_id === $sop->unit_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Biasanya hanya pengusul yang membuat
        return $user->hasRole('pengusul');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sop $sop): bool
    {
        // [PERUBAHAN UTAMA]
        // Kita HAPUS blok "if admin return true".
        // Sekarang Admin pun TIDAK BISA edit konten SOP.

        // 1. Pastikan user adalah PENGUSUL
        if (! $user->hasRole('pengusul')) {
            return false;
        }

        // 2. Pastikan SOP ini milik unit si Pengusul
        if ($user->unit_id !== $sop->unit_id) {
            return false;
        }

        // 3. Hanya status tertentu yang boleh diedit
        // - Draft: Edit biasa
        // - Ditolak: Edit revisi
        // - Disetujui: Edit untuk mengajukan perubahan (Re-submission)
        return in_array($sop->status, ['Draft', 'Ditolak', 'Disetujui']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sop $sop): bool
    {
        // Admin boleh delete jika terpaksa (opsional, bisa dihapus jika ingin strict)
        if ($user->hasRole('admin')) {
            return true;
        }

        // Pengusul hanya boleh hapus Draft atau Ditolak
        if ($user->hasRole('pengusul') && $user->unit_id === $sop->unit_id) {
            return in_array($sop->status, ['Draft', 'Ditolak']);
        }

        return false;
    }
}