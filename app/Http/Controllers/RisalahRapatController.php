<?php

namespace App\Http\Controllers;

use App\Concerns\LogsAudit;
use App\Models\RisalahRapat;
use Illuminate\Http\Request;

class RisalahRapatController extends Controller
{
    use LogsAudit;

    private array $rules = [
        'nomor' => 'nullable|string|max:100',
        'judul' => 'required|string|max:255',
        'tanggal' => 'required|date',
        'waktu' => 'nullable|string|max:50',
        'tempat' => 'nullable|string|max:255',
        'pemimpin' => 'nullable|string|max:255',
        'peserta' => 'nullable|string',
        'agenda' => 'nullable|string',
        'pembahasan' => 'nullable|string',
        'keputusan' => 'nullable|string',
        'tindak_lanjut' => 'nullable|string',
        'lampiran' => 'nullable|string|max:255',
    ];

    private function authorizeRisalah(string $mode = 'read'): void
    {
        $user = auth()->user();
        $perm = \App\Models\RolePermission::where('role_id', $user?->role_id)
            ->where('perm_key', 'risalah')
            ->first();

        abort_if(!$perm, 403, 'Role Anda tidak memiliki akses ke modul Risalah Rapat.');
        abort_if($mode === 'write' && !$perm->can_write, 403, 'Role Anda hanya bisa melihat modul Risalah Rapat.');
    }

    public function index()
    {
        $this->authorizeRisalah('read');
        $items = RisalahRapat::orderByDesc('tanggal')->paginate(20);
        return view('risalah.index', compact('items'));
    }

    public function create()
    {
        $this->authorizeRisalah('write');
        return view('risalah.form', ['item' => null]);
    }

    public function store(Request $request)
    {
        $this->authorizeRisalah('write');
        $data = $request->validate($this->rules);
        $data['dibuat_oleh'] = auth()->user()->nama_lengkap;
        $item = RisalahRapat::create($data);
        $this->audit('CREATE', 'Risalah Rapat', 'Risalah Rapat', $item->id, 'Menambah risalah rapat');
        return redirect()->route('risalah.index')->with('status', 'Risalah rapat ditambahkan.');
    }

    public function edit(RisalahRapat $risalah)
    {
        $this->authorizeRisalah('write');
        return view('risalah.form', ['item' => $risalah]);
    }

    public function update(Request $request, RisalahRapat $risalah)
    {
        $this->authorizeRisalah('write');
        $data = $request->validate($this->rules);
        $risalah->update($data);
        $this->audit('UPDATE', 'Risalah Rapat', 'Risalah Rapat', $risalah->id, 'Mengubah risalah rapat');
        return redirect()->route('risalah.index')->with('status', 'Risalah rapat diperbarui.');
    }

    public function destroy(RisalahRapat $risalah)
    {
        $this->authorizeRisalah('write');
        $id = $risalah->id;
        $risalah->delete();
        $this->audit('DELETE', 'Risalah Rapat', 'Risalah Rapat', $id, 'Menghapus risalah rapat');
        return redirect()->route('risalah.index')->with('status', 'Risalah rapat dihapus.');
    }
}
