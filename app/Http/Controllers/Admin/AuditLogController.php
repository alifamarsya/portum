<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil data Audit Log dengan Filter Gabungan (search)
        $items = AuditLog::with('user')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('username', 'like', "%{$request->search}%")
                          ->orWhere('modul', 'like', "%{$request->search}%");
                });
            })
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        // 2. Verifikasi Rantai Hash per Baris (Hash Chain Verification)
        $expectedPrev = null;
        $allLogs = AuditLog::orderBy('id', 'asc')->get();
        $tamperedIds = [];

        foreach ($allLogs as $log) {
            $expectedHash = hash('sha256', $log->prev_hash . '|' . $log->aksi . '|' . $log->modul . '|'
                . $log->entitas . '|' . $log->entitas_id . '|' . $log->keterangan . '|' . $log->created_at);

            if (!hash_equals($expectedHash, $log->hash) || $log->prev_hash !== $expectedPrev) {
                $tamperedIds[] = $log->id;
            }
            $expectedPrev = $log->hash;
        }

        // Tandai item paginated jika termasuk yang dimanipulasi
        $items->getCollection()->transform(function ($log) use ($tamperedIds) {
            $log->is_tampered = in_array($log->id, $tamperedIds);
            return $log;
        });

        return view('admin.audit-log.index', compact('items', 'tamperedIds'));
    }

    public function rehash()
    {
        $previousHash = null;
        $logs = AuditLog::orderBy('id', 'asc')->get();

        foreach ($logs as $log) {
            $newHash = hash('sha256', $previousHash . '|' . $log->aksi . '|' . $log->modul . '|'
                . $log->entitas . '|' . $log->entitas_id . '|' . $log->keterangan . '|' . $log->created_at);
            
            $log->update([
                'prev_hash' => $previousHash,
                'hash' => $newHash,
            ]);
            $previousHash = $newHash;
        }

        return back()->with('success', 'Rantai hash berhasil disinkronkan kembali!');
    }
}