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

    public function index()
    {
        $items = RisalahRapat::orderByDesc('tanggal')->paginate(20);
        return view('risalah.index', compact('items'));
    }

    public function create()
    {
        return view('risalah.form', ['item' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);
        $data['dibuat_oleh'] = auth()->user()->nama_lengkap;
        $item = RisalahRapat::create($data);
        $this->audit('CREATE', 'Risalah Rapat', 'Risalah Rapat', $item->id, 'Menambah risalah rapat');
        return redirect()->route('risalah.index')->with('status', 'Risalah rapat ditambahkan.');
    }

    public function edit(RisalahRapat $risalah)
    {
        return view('risalah.form', ['item' => $risalah]);
    }

    public function update(Request $request, RisalahRapat $risalah)
    {
        $data = $request->validate($this->rules);
        $risalah->update($data);
        $this->audit('UPDATE', 'Risalah Rapat', 'Risalah Rapat', $risalah->id, 'Mengubah risalah rapat');
        return redirect()->route('risalah.index')->with('status', 'Risalah rapat diperbarui.');
    }

    public function destroy(RisalahRapat $risalah)
    {
        $id = $risalah->id;
        $risalah->delete();
        $this->audit('DELETE', 'Risalah Rapat', 'Risalah Rapat', $id, 'Menghapus risalah rapat');
        return redirect()->route('risalah.index')->with('status', 'Risalah rapat dihapus.');
    }
}
