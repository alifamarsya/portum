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

{{-- Banner Panduan Format Password Default --}}
<div class="mb-6 bg-gradient-to-r from-blue-50/90 via-sky-50/70 to-indigo-50/60 border border-blue-200/90 rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xs">
    <div class="flex items-start gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-[#114E84] text-white flex items-center justify-center flex-shrink-0 shadow-xs mt-0.5">
            @include('partials.icon', ['name' => 'key', 'class' => 'w-5 h-5 text-white'])
        </div>
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="font-bold text-ink text-sm sm:text-base">Format Password Default Akun</h3>
                <span class="px-2.5 py-0.5 rounded-lg bg-white border border-blue-200 text-[#114E84] font-mono font-bold text-xs shadow-2xs">
                    {username}2026
                </span>
            </div>
            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                Setiap pengguna yang password-nya direset oleh admin atau akun yang baru dibuat otomatis menggunakan pola <span class="font-mono text-[#114E84] font-bold">{username}2026</span> (misal: user <span class="font-mono text-slate-800 font-semibold">operator</span> &rarr; password <span class="font-mono text-[#114E84] font-bold">operator2026</span>). Pengguna diwajibkan membuat password baru saat pertama kali login.
            </p>
        </div>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/90 border border-blue-200 text-xs font-semibold text-[#114E84] shadow-2xs">
            @include('partials.icon', ['name' => 'shield', 'class' => 'w-3.5 h-3.5 text-[#114E84]'])
            Wajib Ganti Saat Login
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left: Users Table --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-ink text-sm sm:text-base">Daftar Pengguna Sistem</h2>
                <p class="text-xs text-slate-500">Total {{ $items->count() }} pengguna aktif terdaftar di sistem</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-left text-[11px] uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-3 font-bold whitespace-nowrap">User &amp; Bagian</th>
                        <th class="px-4 py-3 font-bold whitespace-nowrap">Role / Peran</th>
                        <th class="px-4 py-3 font-bold whitespace-nowrap">Status &amp; Password</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($items as $u)
                        @php
                            $isMutlak = in_array($u->role?->nama, ['admin', 'pimpinan', 'operator', 'kabag_umum', 'kabag_aset', 'kabag_pengadaan']);
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
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $isMutlak ? 'bg-slate-100 text-slate-700 border border-slate-200' : 'bg-blue-50 text-[#114E84] border border-blue-200' }}">
                                    {{ $u->role?->label ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex flex-col gap-1.5">
                                    <div>
                                        <span @class([
                                            'px-2 py-0.5 rounded-full text-[10px] font-semibold inline-flex items-center gap-1',
                                            'bg-emerald-50 text-emerald-700 border border-emerald-200' => $u->is_active,
                                            'bg-slate-100 text-slate-500 border border-slate-200' => !$u->is_active,
                                        ])>
                                            <span @class(['w-1.5 h-1.5 rounded-full', 'bg-emerald-500' => $u->is_active, 'bg-slate-400' => !$u->is_active])></span>
                                            {{ $u->is_active ? 'Akun Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                    @if ($u->must_change_pwd)
                                        <div class="flex items-center gap-1">
                                            <button type="button"
                                                    onclick="showUserCredential({{ json_encode([
                                                        'username' => $u->username,
                                                        'nama_lengkap' => $u->nama_lengkap,
                                                        'password' => $u->username . '2026',
                                                        'role_label' => $u->role?->label ?? '-',
                                                        'is_reset' => false,
                                                        'title' => 'Informasi Kredensial Akun',
                                                        'subtitle' => 'Pengguna ini masih menggunakan password default sistem.',
                                                    ]) }})"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200/90 transition text-left"
                                                    title="Klik untuk melihat detail kredensial">
                                                @include('partials.icon', ['name' => 'key', 'class' => 'w-3 h-3 text-amber-600'])
                                                <span class="text-[10px] text-amber-600 font-semibold uppercase tracking-wide">Default:</span>
                                                <code class="font-mono font-bold text-amber-900">{{ $u->username }}2026</code>
                                            </button>
                                            <button type="button"
                                                    onclick="copyText('{{ $u->username }}2026', 'Password default {{ $u->username }}')"
                                                    class="p-1 rounded-md text-amber-700 hover:text-amber-900 hover:bg-amber-100 border border-amber-200/60 transition"
                                                    title="Salin password default {{ $u->username }}2026">
                                                @include('partials.icon', ['name' => 'copy', 'class' => 'w-3 h-3'])
                                            </button>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-50 text-slate-500 border border-slate-200/70" title="Password telah diubah secara mandiri oleh pengguna">
                                            @include('partials.icon', ['name' => 'lock', 'class' => 'w-3 h-3 text-slate-400'])
                                            Password Kustom
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2 text-xs">
                                    <button type="button"
                                            onclick="openEditModal({{ json_encode([
                                                'id' => $u->id,
                                                'username' => $u->username,
                                                'nama_lengkap' => $u->nama_lengkap,
                                                'email' => $u->email,
                                                'jabatan' => $u->jabatan,
                                                'bagian' => $u->bagian,
                                                'role_id' => $u->role_id,
                                                'is_active' => (bool)$u->is_active,
                                                'role_nama' => $u->role?->nama,
                                            ]) }})"
                                            class="px-2.5 py-1 rounded-lg text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/80 font-semibold transition flex items-center gap-1">
                                        @include('partials.icon', ['name' => 'pencil', 'class' => 'w-3 h-3'])
                                        Edit
                                    </button>

                                    <form method="POST" action="{{ route('admin.users.reset-password', $u) }}" class="inline" onsubmit="return confirm('Reset password pengguna {{ $u->username }} ke password default ({{ $u->username }}2026)?\n\nSetelah direset, pengguna akan menggunakan password \'{{ $u->username }}2026\' dan diwajibkan menggantinya saat login.')">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg text-[#114E84] bg-blue-50/80 hover:bg-blue-100 border border-blue-200/80 font-semibold transition flex items-center gap-1" title="Reset ke password default {{ $u->username }}2026">
                                            @include('partials.icon', ['name' => 'key', 'class' => 'w-3 h-3 text-[#114E84]'])
                                            Reset
                                        </button>
                                    </form>

                                    @if (!$isMutlak)
                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline" onsubmit="return confirm('Hapus pengguna {{ $u->username }}?')">
                                            @csrf @method('DELETE')
                                            <button class="px-2 py-1 rounded-lg text-rose-600 hover:bg-rose-50 font-medium transition">Hapus</button>
                                        </form>
                                    @else
                                        <span class="px-2 py-1 text-slate-300 select-none cursor-not-allowed text-[11px]" title="Role mutlak tidak dapat dihapus">Mutlak</span>
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
                <p class="text-xs text-slate-500">Buat akun untuk staf bagian atau cabang/pemohon</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Peran / Role</label>
                <select name="role_id" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition bg-white">
                    @foreach ($creatableRoles as $r)
                        <option value="{{ $r->id }}" @selected($r->nama === 'user')>
                            {{ $r->label }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Role staf 3 bagian &amp; pemohon tiket dapat memiliki lebih dari 1 akun pengguna.</p>
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

@push('modals')
{{-- Modal Edit User --}}
<div id="editUserModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 sm:p-6 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeEditModal()"></div>

    <div class="relative w-full max-w-lg my-auto bg-white rounded-2xl text-left shadow-2xl border border-slate-200 overflow-hidden z-10">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-700 flex items-center justify-center">
                    @include('partials.icon', ['name' => 'pencil', 'class' => 'w-4 h-4 text-amber-600'])
                </div>
                <div>
                    <h3 class="font-bold text-ink text-base" id="modal-title">Edit Data Pengguna</h3>
                    <p class="text-xs text-slate-500">Perbarui username, role jabatan, profil, atau status akun</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 transition p-1.5 rounded-lg hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="editUserForm" method="POST" action="" class="p-5 sm:p-6 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Username <span class="text-rose-500">*</span></label>
                    <input type="text" name="username" id="edit_username" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Peran / Role <span class="text-rose-500">*</span></label>
                    <select name="role_id" id="edit_role_id" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition bg-white">
                        @foreach ($roles as $r)
                            @php
                                $isMutlak = in_array($r->nama, ['admin', 'pimpinan', 'operator', 'kepala_bagian', 'kepala_divisi']);
                            @endphp
                            <option value="{{ $r->id }}" data-nama="{{ $r->nama }}" data-is-mutlak="{{ $isMutlak ? '1' : '0' }}" data-count="{{ $r->users_count }}" data-base-label="{{ $r->label }}">
                                {{ $r->label }} {{ $isMutlak ? '(Peran Mutlak)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_lengkap" id="edit_nama_lengkap" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" id="edit_email" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan</label>
                    <input type="text" name="jabatan" id="edit_jabatan" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Bagian / Kantor Cabang</label>
                <input type="text" name="bagian" id="edit_bagian" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
            </div>

            <div class="pt-2 border-t border-slate-100">
                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="w-4 h-4 rounded text-[#114E84] border-slate-300 focus:ring-[#114E84]">
                    <span class="text-xs font-semibold text-slate-700">Status Akun Aktif (Bisa Login)</span>
                </label>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#114E84] text-white text-xs font-bold hover:bg-[#0E4272] transition shadow-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Detail Kredensial User (Persisten & Bisa Disalin) --}}
<div id="credentialModal" class="fixed inset-0 z-[110] hidden items-center justify-center p-4 sm:p-6 overflow-y-auto" aria-labelledby="credential-modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeCredentialModal()"></div>

    <div class="relative w-full max-w-md my-auto bg-white rounded-2xl text-left shadow-2xl border border-slate-200 overflow-hidden z-10 animate-enter">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#114E84]/10 text-[#114E84] flex items-center justify-center flex-shrink-0">
                    @include('partials.icon', ['name' => 'key', 'class' => 'w-5 h-5 text-[#114E84]'])
                </div>
                <div>
                    <h3 class="font-bold text-ink text-base" id="cred_title">Kredensial Pengguna</h3>
                    <p class="text-xs text-slate-500" id="cred_subtitle">Detail password default akun</p>
                </div>
            </div>
            <button type="button" onclick="closeCredentialModal()" class="text-slate-400 hover:text-slate-600 transition p-1.5 rounded-lg hover:bg-slate-100">
                @include('partials.icon', ['name' => 'x', 'class' => 'w-5 h-5'])
            </button>
        </div>

        <div class="p-5 sm:p-6 space-y-4">
            {{-- Info User --}}
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-[#114E84] text-white flex items-center justify-center font-bold text-xs" id="cred_avatar">
                    U
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-ink font-bold text-xs truncate" id="cred_nama_lengkap">-</p>
                    <p class="text-slate-400 text-[11px] truncate" id="cred_role_label">-</p>
                </div>
            </div>

            {{-- Username field --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Username Login</label>
                <div class="flex items-center gap-2">
                    <input type="text" id="cred_username" readonly class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink font-mono font-bold select-all focus:outline-none">
                    <button type="button" onclick="copyFromInput('cred_username', 'Username')" class="px-3 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
                        @include('partials.icon', ['name' => 'copy', 'class' => 'w-3.5 h-3.5 text-slate-500'])
                        Salin
                    </button>
                </div>
            </div>

            {{-- Password field --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-semibold text-slate-600">Password Default (Sementara)</label>
                    <button type="button" onclick="togglePasswordVisibility()" class="text-[11px] text-[#114E84] hover:underline font-medium flex items-center gap-1">
                        @include('partials.icon', ['name' => 'eye', 'class' => 'w-3 h-3 text-[#114E84]'])
                        <span id="pwd_toggle_label">Sembunyikan</span>
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <input type="text" id="cred_password" readonly class="flex-1 bg-amber-50/70 border border-amber-200 rounded-xl px-3.5 py-2.5 text-xs text-amber-900 font-mono font-bold select-all focus:outline-none">
                    <button type="button" onclick="copyFromInput('cred_password', 'Password')" class="px-3 py-2.5 rounded-xl border border-amber-200 bg-amber-500 text-white hover:bg-amber-600 text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                        @include('partials.icon', ['name' => 'copy', 'class' => 'w-3.5 h-3.5 text-white'])
                        Salin
                    </button>
                </div>
            </div>

            {{-- Catatan Peringatan --}}
            <div class="p-3 bg-blue-50/80 rounded-xl border border-blue-100 flex items-start gap-2.5 text-xs text-blue-900">
                @include('partials.icon', ['name' => 'alert-circle', 'class' => 'w-4 h-4 text-[#114E84] flex-shrink-0 mt-0.5'])
                <p class="leading-relaxed text-[11px]">
                    Pengguna <strong>diwajibkan mengganti password baru</strong> saat pertama kali login menggunakan kredensial ini. Admin dapat menyalin info kredensial di atas untuk diberikan kepada pengguna.
                </p>
            </div>

            {{-- Tombol Aksi --}}
            <div class="pt-2 flex flex-col sm:flex-row items-center gap-2">
                <button type="button" onclick="copyAllCredentials()" class="w-full py-2.5 px-4 rounded-xl bg-[#114E84] text-white hover:bg-[#0E4272] text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                    @include('partials.icon', ['name' => 'clipboard', 'class' => 'w-4 h-4 text-white'])
                    Salin Semua Kredensial
                </button>
                <button type="button" onclick="closeCredentialModal()" class="w-full sm:w-auto py-2.5 px-5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toast Feedback Kopi --}}
<div id="copyToast" class="fixed bottom-6 right-6 z-[120] hidden items-center gap-2 bg-slate-900 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xl border border-slate-700/60 transition-all duration-200">
    @include('partials.icon', ['name' => 'check', 'class' => 'w-4 h-4 text-emerald-400'])
    <span id="copyToastMessage">Berhasil disalin!</span>
</div>
@endpush

<script>
    function openEditModal(user) {
        const modal = document.getElementById('editUserModal');
        const form = document.getElementById('editUserForm');
        form.action = "{{ url('admin/users') }}/" + user.id;

        document.getElementById('edit_username').value = user.username || '';
        document.getElementById('edit_nama_lengkap').value = user.nama_lengkap || '';
        document.getElementById('edit_email').value = user.email || '';
        document.getElementById('edit_jabatan').value = user.jabatan || '';
        document.getElementById('edit_bagian').value = user.bagian || '';
        document.getElementById('edit_is_active').checked = Boolean(user.is_active);

        const selectRole = document.getElementById('edit_role_id');
        const mutlakRoles = ['admin', 'pimpinan', 'operator', 'kepala_bagian', 'kepala_divisi'];

        Array.from(selectRole.options).forEach(opt => {
            const roleNama = opt.getAttribute('data-nama');
            const isMutlak = mutlakRoles.includes(roleNama);
            const userCount = parseInt(opt.getAttribute('data-count') || '0', 10);
            const optVal = parseInt(opt.value, 10);
            const baseLabel = opt.getAttribute('data-base-label') || opt.text;

            if (isMutlak && userCount >= 1 && optVal !== user.role_id) {
                opt.disabled = true;
                opt.textContent = baseLabel + ' (Mutlak - Sudah Terisi)';
            } else {
                opt.disabled = false;
                opt.textContent = baseLabel + (isMutlak ? ' (Mutlak)' : '');
            }
        });

        selectRole.value = user.role_id;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        const modal = document.getElementById('editUserModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    // Modal Kredensial Persisten
    let currentRawPassword = '';

    function showUserCredential(data) {
        document.getElementById('cred_title').textContent = data.title || 'Kredensial Pengguna';
        document.getElementById('cred_subtitle').textContent = data.subtitle || 'Detail password default akun';
        document.getElementById('cred_nama_lengkap').textContent = data.nama_lengkap || data.username;
        document.getElementById('cred_role_label').textContent = (data.role_label || 'Pengguna') + ' • ' + data.username;
        document.getElementById('cred_avatar').textContent = (data.nama_lengkap || data.username || 'U').charAt(0).toUpperCase();

        document.getElementById('cred_username').value = data.username || '';
        currentRawPassword = data.password || '';
        const pwdInput = document.getElementById('cred_password');
        pwdInput.value = currentRawPassword;
        pwdInput.type = 'text';
        document.getElementById('pwd_toggle_label').textContent = 'Sembunyikan';

        const modal = document.getElementById('credentialModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeCredentialModal() {
        const modal = document.getElementById('credentialModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function togglePasswordVisibility() {
        const pwdInput = document.getElementById('cred_password');
        const label = document.getElementById('pwd_toggle_label');
        if (pwdInput.type === 'text') {
            pwdInput.type = 'password';
            label.textContent = 'Lihat';
        } else {
            pwdInput.type = 'text';
            label.textContent = 'Sembunyikan';
        }
    }

    let toastTimer = null;
    function copyText(text, label) {
        if (!navigator.clipboard) {
            const temp = document.createElement('textarea');
            temp.value = text;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
        } else {
            navigator.clipboard.writeText(text);
        }

        const toast = document.getElementById('copyToast');
        const msg = document.getElementById('copyToastMessage');
        msg.textContent = (label || 'Teks') + ' berhasil disalin!';
        toast.classList.remove('hidden');
        toast.classList.add('flex');

        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.classList.add('hidden');
            toast.classList.remove('flex');
        }, 2200);
    }

    function copyFromInput(inputId, label) {
        const val = document.getElementById(inputId).value;
        copyText(val, label);
    }

    function copyAllCredentials() {
        const u = document.getElementById('cred_username').value;
        const p = currentRawPassword || document.getElementById('cred_password').value;
        const text = `Username: ${u}\nPassword: ${p}`;
        copyText(text, 'Kredensial lengkap');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeEditModal();
            closeCredentialModal();
        }
    });

    // Otomatis buka modal kredensial jika baru saja direset atau dibuat oleh admin
    @if (session('credential_modal'))
        document.addEventListener('DOMContentLoaded', function () {
            showUserCredential(@json(session('credential_modal')));
        });
    @endif
</script>
@endsection
