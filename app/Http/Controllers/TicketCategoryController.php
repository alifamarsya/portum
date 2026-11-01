<?php

namespace App\Http\Controllers;

use App\Models\TicketCategory;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{

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
