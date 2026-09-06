@extends('layouts.app')
@section('title', 'Manajemen User')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 mb-1.5">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#114E84]/10 text-[#114E84] border border-[#114E84]/20">
            @include('partials.icon', ['name' => 'users', 'class' => 'w-3 h-3 text-[#114E84]'])
            User Directory
        </span>
        <span class="text-slate-300">•</span>
        <span class="text-xs text-slate-500 font-medium">Pengguna &amp; Akun Cabang</span>
    </div>
    <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">
        Manajemen Pengguna (User)
    </h1>
    <p class="text-xs sm:text-sm text-slate-500 mt-1">
        Kelola akun pengguna operasional bank dan akun pemohon tiket layanan dari kantor cabang &amp; divisi.
    </p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left: Users Table --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-ink text-sm sm:text-base">Daftar Pengguna Sistem</h2>
                <p class="text-xs text-slate-500">Total {{ $items->count() }} pengguna aktif terdaftar di sistem</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-blue-50 text-[#114E84]">
                {{ $items->where('role.nama', 'user')->count() }} Pemohon Cabang/Divisi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-left text-[11px] uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-3 font-bold whitespace-nowrap">User &amp; Bagian</th>
                        <th class="px-4 py-3 font-bold whitespace-nowrap">Role / Peran</th>
                        <th class="px-4 py-3 font-bold whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($items as $u)
                        @php
                            $isMulti = $u->role?->nama === 'user';
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-[#114E84]/10 text-[#114E84] flex items-center justify-center text-xs font-bold flex-shrink-0">
                                        {{ strtoupper(substr($u->nama_lengkap, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 leading-tight">
                                        <p class="text-ink font-bold truncate text-[13px]">{{ $u->nama_lengkap }}</p>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="text-slate-400 text-[11px] font-mono">{{ $u->username }}</span>
                                            @if ($u->bagian)
                                                <span class="text-slate-300">•</span>
                                                <span class="text-slate-500 text-[11px] truncate">{{ $u->bagian }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $isMulti ? 'bg-blue-50 text-[#114E84] border border-blue-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                    {{ $u->role?->label ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span @class([
                                    'px-2.5 py-1 rounded-full text-[11px] font-semibold inline-flex items-center gap-1.5',
                                    'bg-emerald-50 text-emerald-700 border border-emerald-200' => $u->is_active,
                                    'bg-slate-100 text-slate-500 border border-slate-200' => !$u->is_active,
                                ])>
                                    <span @class(['w-1.5 h-1.5 rounded-full', 'bg-emerald-500' => $u->is_active, 'bg-slate-400' => !$u->is_active])></span>
                                    {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-3 text-xs">
                                    <form method="POST" action="{{ route('admin.users.reset-password', $u) }}" class="inline" onsubmit="return confirm('Reset password pengguna {{ $u->username }}?')">
                                        @csrf
                                        <button class="text-[#114E84] font-semibold hover:underline">Reset Password</button>
                                    </form>

                                    @if ($isMulti)
                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline" onsubmit="return confirm('Hapus pengguna pemohon {{ $u->username }}?')">
                                            @csrf @method('DELETE')
                                            <button class="text-rose-500 hover:text-rose-700 font-medium transition">Hapus</button>
                                        </form>
                                    @else
                                        <span class="text-slate-300 select-none" title="Role akun operasional tunggal tidak dapat dihapus">Hapus</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Right: Create User Form --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-5 sm:p-6 h-fit">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-8 h-8 rounded-lg bg-[#114E84]/10 text-[#114E84] flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-[#114E84]'])
            </div>
            <div>
                <h2 class="font-bold text-ink text-base">Tambah Pengguna Baru</h2>
                <p class="text-xs text-slate-500">Buat akun untuk kantor cabang / pemohon</p>
            </div>
        </div>

        <div class="p-3 rounded-xl bg-blue-50/70 border border-blue-200/60 text-[11.5px] text-slate-600 mb-4 leading-relaxed">
            <p class="font-bold text-ink mb-0.5">Ketentuan Role:</p>
            Role <strong>User (Pemohon Layanan)</strong> dapat dibuat berulang kali untuk cabang &amp; divisi. Role lainnya masing-masing hanya memiliki 1 akun tunggal.
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Peran / Role</label>
                <select name="role_id" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition bg-white">
                    @foreach ($roles as $r)
                        @php
                            $isSingleAndFilled = ($r->nama !== 'user' && $r->users_count >= 1);
                        @endphp
                        <option value="{{ $r->id }}"
                                @disabled($isSingleAndFilled)
                                @selected($r->nama === 'user')>
                            {{ $r->label }} {{ $r->nama === 'user' ? '(Multi-User / Kantor Cabang & Divisi)' : ($isSingleAndFilled ? '(1 User - Sudah Terisi)' : '(1 User)') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Username</label>
                <input name="username" placeholder="Contoh: user_cabang_luwuk" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                <input name="nama_lengkap" placeholder="Nama lengkap pegawai" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email (Opsional)</label>
                <input name="email" type="email" placeholder="pegawai@banksulteng.co.id" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan</label>
                <input name="jabatan" placeholder="Contoh: Staff Operasional Cabang" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Bagian / Kantor Cabang</label>
                <input name="bagian" placeholder="Contoh: Kantor Cabang Luwuk" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
            </div>

            <button type="submit" class="w-full mt-2 bg-[#114E84] text-white text-xs font-bold py-3 rounded-xl hover:bg-[#0E4272] transition shadow-xs">
                Buat Akun Pengguna
            </button>
        </form>
    </div>
</div>
@endsection
