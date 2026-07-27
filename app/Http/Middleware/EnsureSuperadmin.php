<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Halaman Manajemen User/Role & Audit Log hanya untuk superadmin --
// menu-nya juga disembunyikan di sidebar untuk role lain, tapi
// route-nya tetap harus ditutup di sisi server (jangan andalkan UI saja).
class EnsureSuperadmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->role?->nama === 'superadmin', 403, 'Halaman ini khusus Super Administrator.');
        return $next($request);
    }
}
