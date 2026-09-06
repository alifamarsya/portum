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
                            $isMutlak = in_array($u->role?->nama, ['admin', 'pimpinan', 'kepala_bagian', 'kepala_divisi']);
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
                                <div class="inline-flex items-center gap-2.5 text-xs">
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

                                    <form method="POST" action="{{ route('admin.users.reset-password', $u) }}" class="inline" onsubmit="return confirm('Reset password pengguna {{ $u->username }}?')">
                                        @csrf
                                        <button class="px-2.5 py-1 rounded-lg text-[#114E84] bg-blue-50/80 hover:bg-blue-100 border border-blue-200/80 font-semibold transition">
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

{{-- Modal Edit User --}}
<div id="editUserModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" onclick="closeEditModal()"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200">
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
                                    $isMutlak = in_array($r->nama, ['admin', 'pimpinan', 'kepala_bagian', 'kepala_divisi']);
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
</div>

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
        const mutlakRoles = ['admin', 'pimpinan', 'kepala_bagian', 'kepala_divisi'];

        Array.from(selectRole.options).forEach(opt => {
            const roleNama = opt.getAttribute('data-nama');
            const isMutlak = mutlakRoles.includes(roleNama);
            const userCount = parseInt(opt.getAttribute('data-count') || '0', 10);
            const optVal = parseInt(opt.value, 10);
            const baseLabel = opt.getAttribute('data-base-label') || opt.text;

            // Jika role mutlak dan sudah diisi user lain (bukan user yang sedang diedit), disable
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
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        const modal = document.getElementById('editUserModal');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeEditModal();
        }
    });
</script>
@endsection
