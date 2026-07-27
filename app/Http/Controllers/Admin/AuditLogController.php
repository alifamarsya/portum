<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $items = AuditLog::with('user')
            ->when($request->modul, fn ($q) => $q->where('modul', 'like', "%{$request->modul}%"))
            ->when($request->username, fn ($q) => $q->where('username', 'like', "%{$request->username}%"))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit-log.index', compact('items'));
    }
}
