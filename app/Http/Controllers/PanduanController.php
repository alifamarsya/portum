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

    public function index()
    {
        $items = Panduan::orderBy('kategori')->orderBy('urutan')->get();
        return view('panduan.index', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);
        $data['updated_by'] = auth()->user()->nama_lengkap;
        $item = Panduan::create($data);
        $this->audit('CREATE', 'Panduan', 'Panduan', $item->id, 'Menambah panduan');
        return back()->with('status', 'Panduan ditambahkan.');
    }

    public function update(Request $request, Panduan $panduan)
    {
        $data = $request->validate($this->rules);
        $data['updated_by'] = auth()->user()->nama_lengkap;
        $panduan->update($data);
        $this->audit('UPDATE', 'Panduan', 'Panduan', $panduan->id, 'Mengubah panduan');
        return back()->with('status', 'Panduan diperbarui.');
    }

    public function destroy(Panduan $panduan)
    {
        $id = $panduan->id;
        $panduan->delete();
        $this->audit('DELETE', 'Panduan', 'Panduan', $id, 'Menghapus panduan');
        return back()->with('status', 'Panduan dihapus.');
    }
}
