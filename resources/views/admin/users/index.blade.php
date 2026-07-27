@extends('layouts.app')
@section('title', 'Manajemen User')
@section('content')
    <div class="mb-5">
        <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">Pengaturan</p>
        <h1 class="text-xl font-bold text-ink">Manajemen User</h1>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-left text-[12px] uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">User</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Role</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($items as $u)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand to-brand-light text-white flex items-center justify-center text-xs font-semibold flex-shrink-0">
                                            {{ strtoupper(substr($u->nama_lengkap, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 leading-tight">
                                            <p class="text-ink font-medium truncate">{{ $u->nama_lengkap }}</p>
                                            <p class="text-slate-400 text-[12px] font-mono truncate">{{ $u->username }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">{{ $u->role->label }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'px-2.5 py-1 rounded-full text-xs font-medium inline-flex items-center gap-1',
                                        'bg-emerald-50 text-emerald-700' => $u->is_active,
                                        'bg-slate-100 text-slate-500' => !$u->is_active,
                                    ])>
                                        <span @class(['w-1.5 h-1.5 rounded-full', 'bg-emerald-500' => $u->is_active, 'bg-slate-400' => !$u->is_active])></span>
                                        {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-3 text-[13px]">
                                        <form method="POST" action="{{ route('admin.users.reset-password', $u) }}" class="inline" onsubmit="return confirm('Reset password user ini?')">
                                            @csrf
                                            <button class="text-brand font-medium hover:underline">Reset Password</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline" onsubmit="return confirm('Hapus user ini?')">
                                            @csrf @method('DELETE')
                                            <button class="text-slate-400 hover:text-red-600 transition">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-card p-5 h-fit">
            <h2 class="font-semibold text-ink mb-3 flex items-center gap-2">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-gold'])
                Tambah User
            </h2>
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3">
                @csrf
                <input name="username" placeholder="Username" required class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                <input name="nama_lengkap" placeholder="Nama Lengkap" required class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                <input name="email" placeholder="Email (opsional)" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                <input name="jabatan" placeholder="Jabatan" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                <input name="bagian" placeholder="Bagian" class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                <select name="role_id" required class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
                    @foreach ($roles as $r) <option value="{{ $r->id }}">{{ $r->label }}</option> @endforeach
                </select>
                <button class="w-full bg-brand text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-brand-light transition">Buat User</button>
            </form>
        </div>
    </div>
@endsection
