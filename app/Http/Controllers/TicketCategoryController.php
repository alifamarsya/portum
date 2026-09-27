<?php

namespace App\Http\Controllers;

use App\Concerns\LogsAudit;
use App\Models\InternalDepartment;
use App\Models\TicketCategory;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{
    use LogsAudit;

    /**
     * Tampilkan daftar kategori tiket dengan filter dan form kelola.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->isOperator() && !$user->isSuperAdmin()) {
            abort(403, 'Hanya Operator Helpdesk dan Administrator yang berwenang mengelola kategori tiket.');
        }

        $query = TicketCategory::with('department')->orderBy('department_id')->orderBy('sort_order')->orderBy('name');

        if ($request->filled('jenis_pengajuan')) {
            $query->where('jenis_pengajuan', $request->jenis_pengajuan);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $categories = $query->paginate(20)->withQueryString();
        $departments = InternalDepartment::all();

        $stats = [
            'total' => TicketCategory::count(),
            'permasalahan' => TicketCategory::where('jenis_pengajuan', 'Permasalahan')->count(),
            'permintaan' => TicketCategory::where('jenis_pengajuan', 'Permintaan')->count(),
            'aktif' => TicketCategory::where('is_active', true)->count(),
        ];

        return view('categories.index', compact('categories', 'departments', 'stats'));
    }

    /**
     * Simpan kategori tiket baru.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isOperator() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'jenis_pengajuan' => 'required|in:Permintaan,Permasalahan',
            'sla_resolution_hours' => 'required|integer|min:1|max:720',
            'department_id' => 'nullable|exists:internal_departments,id',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['default_sla_hours'] = $validated['sla_resolution_hours'];
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category = TicketCategory::create($validated);

        $this->audit(
            'CREATE',
            'Kategori Tiket',
            'TicketCategory',
            $category->id,
            "Menambahkan kategori tiket baru '{$category->name}' ({$category->jenis_pengajuan}, SLA: {$category->sla_resolution_hours} jam)"
        );

        return redirect()->route('ticket-categories.index')
            ->with('status', "Kategori '{$category->name}' berhasil ditambahkan.");
    }

    /**
     * Perbarui data kategori tiket.
     */
    public function update(Request $request, TicketCategory $category)
    {
        $user = auth()->user();
        if (!$user->isOperator() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'jenis_pengajuan' => 'required|in:Permintaan,Permasalahan',
            'sla_resolution_hours' => 'required|integer|min:1|max:720',
            'department_id' => 'nullable|exists:internal_departments,id',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['default_sla_hours'] = $validated['sla_resolution_hours'];
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category->update($validated);

        $this->audit(
            'UPDATE',
            'Kategori Tiket',
            'TicketCategory',
            $category->id,
            "Memperbarui kategori tiket '{$category->name}' ({$category->jenis_pengajuan}, SLA: {$category->sla_resolution_hours} jam)"
        );

        return redirect()->route('ticket-categories.index')
            ->with('status', "Kategori '{$category->name}' berhasil diperbarui.");
    }

    /**
     * Toggle status aktif / nonaktif kategori tiket.
     */
    public function toggleStatus(TicketCategory $category)
    {
        $user = auth()->user();
        if (!$user->isOperator() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $category->is_active = !$category->is_active;
        $category->save();

        $statusText = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';

        $this->audit(
            'UPDATE',
            'Kategori Tiket',
            'TicketCategory',
            $category->id,
            "Status kategori '{$category->name}' {$statusText}"
        );

        return back()->with('status', "Kategori '{$category->name}' berhasil {$statusText}.");
    }

    /**
     * Hapus kategori tiket.
     */
    public function destroy(TicketCategory $category)
    {
        $user = auth()->user();
        if (!$user->isOperator() && !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        // Cek jika kategori sudah pernah dipakai pada tiket
        if ($category->tickets()->exists()) {
            // Nonaktifkan saja demi integritas data referensi tiket
            $category->update(['is_active' => false]);
            return back()->with('warning', "Kategori '{$category->name}' telah digunakan oleh tiket existing, sehingga statusnya diubah menjadi non-aktif alih-alih dihapus.");
        }

        $catName = $category->name;
        $category->delete();

        $this->audit(
            'DELETE',
            'Kategori Tiket',
            'TicketCategory',
            $category->id,
            "Menghapus kategori tiket '{$catName}'"
        );

        return redirect()->route('ticket-categories.index')
            ->with('status', "Kategori '{$catName}' berhasil dihapus.");
    }

    /**
     * Endpoint API JSON untuk mengambil kategori aktif berdasarkan Jenis Pengajuan.
     */
    public function getCategoriesByJenis(Request $request)
    {
        $jenis = $request->query('jenis', 'Permintaan');

        $categories = TicketCategory::active()
            ->byJenis($jenis)
            ->with('department:id,name')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'jenis_pengajuan', 'sla_resolution_hours', 'department_id']);

        return response()->json([
            'success' => true,
            'data' => $categories->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'jenis_pengajuan' => $c->jenis_pengajuan,
                    'sla_resolution_hours' => $c->sla_resolution_hours,
                    'department_id' => $c->department_id,
                    'department_name' => $c->department?->name ?? 'Bagian Terkait',
                ];
            }),
        ]);
    }
}
