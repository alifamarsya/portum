<?php

namespace App\Http\Controllers;

use App\Concerns\LogsAudit;
use App\Models\Panduan;
use Illuminate\Http\Request;

class PanduanController extends Controller
{
    use LogsAudit;

    private array $rules = [
        'judul' => 'required|string|max:255',
        'kategori' => 'nullable|string|max:100',
        'konten' => 'nullable|string',
        'urutan' => 'nullable|integer',
    ];

    private function authorizePanduan(string $mode = 'read'): void
    {
        $user = auth()->user();
        $perm = \App\Models\RolePermission::where('role_id', $user?->role_id)
            ->where('perm_key', 'panduan')
            ->first();

        abort_if(!$perm, 403, 'Role Anda tidak memiliki akses ke modul Buku Panduan.');
        abort_if($mode === 'write' && !$perm->can_write, 403, 'Role Anda hanya bisa melihat modul Buku Panduan.');
    }

    public function index()
    {
        $this->authorizePanduan('read');
        $items = Panduan::orderBy('kategori')->orderBy('urutan')->get();
        return view('panduan.index', compact('items'));
    }

    public function store(Request $request)
    {
        $this->authorizePanduan('write');
        $data = $request->validate($this->rules);
        $data['updated_by'] = auth()->user()->nama_lengkap;
        $item = Panduan::create($data);
        $this->audit('CREATE', 'Panduan', 'Panduan', $item->id, 'Menambah panduan');
        return back()->with('status', 'Panduan ditambahkan.');
    }

    public function update(Request $request, Panduan $panduan)
    {
        $this->authorizePanduan('write');
        $data = $request->validate($this->rules);
        $data['updated_by'] = auth()->user()->nama_lengkap;
        $panduan->update($data);
        $this->audit('UPDATE', 'Panduan', 'Panduan', $panduan->id, 'Mengubah panduan');
        return back()->with('status', 'Panduan diperbarui.');
    }

    public function destroy(Panduan $panduan)
    {
        $this->authorizePanduan('write');
        $id = $panduan->id;
        $panduan->delete();
        $this->audit('DELETE', 'Panduan', 'Panduan', $id, 'Menghapus panduan');
        return back()->with('status', 'Panduan dihapus.');
    }
}
