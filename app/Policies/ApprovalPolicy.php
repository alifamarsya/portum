<?php

namespace App\Policies;

use App\Models\User;

// Policy generik untuk semua model yang punya alur Maker-Checker
// (UmBiayaHarian, UmPermintaanCabang, AsPks, AsMemoSewaCabang, PgSpk).
// Aturan inti CPMK Blockchain/akuntabilitas: checker TIDAK BOLEH orang
// yang sama dengan maker -- supaya approval tidak bisa self-approve.
class ApprovalPolicy
{
    public function approve(User $user, object $transaksi): bool
    {
        if ($transaksi->approval_status !== 'Diajukan') {
            return false;
        }

        if ((int) $transaksi->maker_id === (int) $user->id) {
            return false; // maker tidak boleh jadi checker dirinya sendiri
        }

        return true;
    }

    public function reject(User $user, object $transaksi): bool
    {
        return $this->approve($user, $transaksi);
    }
}
