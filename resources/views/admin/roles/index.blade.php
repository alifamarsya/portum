@extends('layouts.app')
@section('title', 'Peran & Hak Akses')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 mb-1.5">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#114E84]/10 text-[#114E84] border border-[#114E84]/20">
                @include('partials.icon', ['name' => 'shield', 'class' => 'w-3 h-3 text-[#114E84]'])
                RBAC Management
            </span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">
            Peran &amp; Hak Akses (Role Permission)
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Atur matriks perizinan modul, tambah peran baru, atau perbarui wewenang baca/tulis seluruh peran operasional sistem Portum.
        </p>
    </div>

    <button type="button"
            onclick="openCreateRoleModal()"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs sm:text-sm font-bold shadow-xs hover:shadow transition flex-shrink-0">
        @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-white'])
        <span>Tambah Role Baru</span>
    </button>
</div>

<div class="space-y-6">
    @foreach ($roles as $role)
        @php
            $isSystem = in_array($role->nama, $systemRoles ?? ['admin', 'pimpinan', 'kabag_umum', 'kabag_aset', 'kabag_pengadaan', 'umum_rt', 'aset', 'pengadaan', 'user', 'operator']);
            $isMutlak = in_array($role->nama, ['admin', 'pimpinan', 'kabag_umum', 'kabag_aset', 'kabag_pengadaan']);
            $userCount = $role->users->count();
            $assignedUsers = $role->users->pluck('username')->implode(', ');
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-5 sm:p-6 transition-all hover:border-[#114E84]/30">
            {{-- Card Header & Role Metadata --}}
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-4 mb-5 border-b border-slate-100">
                <div class="flex items-start sm:items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-[#114E84]/10 text-[#114E84] flex items-center justify-center flex-shrink-0 font-bold text-sm">
                        {{ $loop->iteration }}
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-base font-bold text-ink truncate">{{ $role->label }}</h2>
                            <span class="text-xs font-mono text-slate-400">({{ $role->nama }})</span>
                            @if ($isMutlak)
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    Role Mutlak ({{ $assignedUsers ?: '1 User' }})
                                </span>
                            @elseif ($isSystem)
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-50 text-[#114E84] border border-blue-200">
                                    Peran Sistem ({{ $userCount }} Pengguna)
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Peran Kustom ({{ $userCount }} Pengguna)
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ $role->deskripsi ?: 'Tidak ada deskripsi wewenang khusus.' }}</p>
                    </div>
                </div>

                {{-- Action Buttons: Edit Role, Delete Role, Save Permissions --}}
                <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap justify-end flex-shrink-0">
                    <button type="button"
                            onclick="openEditRoleModal({{ json_encode([
                                'id' => $role->id,
                                'label' => $role->label,
                                'nama' => $role->nama,
                                'deskripsi' => $role->deskripsi,
                                'is_system' => $isSystem,
                            ]) }})"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/80 transition">
                        @include('partials.icon', ['name' => 'pencil', 'class' => 'w-3.5 h-3.5 text-amber-600'])
                        <span>Edit Role</span>
                    </button>

                    @if (!$isSystem)
                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus role \'{{ $role->label }}\'? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    @if ($userCount > 0) disabled title="Role masih digunakan oleh {{ $userCount }} pengguna aktif" @endif
                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold {{ $userCount > 0 ? 'text-slate-300 bg-slate-50 border border-slate-200 cursor-not-allowed' : 'text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200/80 transition' }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                <span>Hapus</span>
                            </button>
                        </form>
                    @endif

                    <button type="submit" form="permForm_{{ $role->id }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold shadow-xs hover:shadow transition">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-amber-300'])
                        <span>Simpan Hak Akses</span>
                    </button>
                </div>
            </div>

            {{-- Grouped Permissions Grid Form --}}
            <form id="permForm_{{ $role->id }}" method="POST" action="{{ route('admin.roles.permissions', $role) }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($groupedPermissions as $groupName => $groupItems)
                        <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50/50 flex flex-col justify-between">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2.5 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#114E84]"></span>
                                    {{ $groupName }}
                                </p>
                                <div class="space-y-2">
                                    @foreach ($groupItems as $permKey => $permMeta)
                                        @php
                                            $perm = $role->permissions->firstWhere('perm_key', $permKey);
                                            $hasAccess = !is_null($perm);
                                            $canWrite = $perm?->can_write ?? false;
                                        @endphp
                                        <div class="p-2.5 rounded-lg border {{ $hasAccess ? 'border-[#114E84]/30 bg-white shadow-2xs' : 'border-slate-200 bg-slate-100/60 opacity-80' }} transition-all">
                                            <div class="flex items-start justify-between gap-2">
                                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                                    <input type="checkbox"
                                                           name="access_{{ $permKey }}"
                                                           value="1"
                                                           @checked($hasAccess)
                                                           class="rounded text-[#114E84] focus:ring-[#114E84] w-3.5 h-3.5">
                                                    <span class="text-xs font-bold text-ink leading-tight">{{ $permMeta['label'] }}</span>
                                                </label>
                                                <span class="text-[10px] font-mono text-slate-400">{{ $permKey }}</span>
                                            </div>
                                            <p class="text-[10.5px] text-slate-500 mt-1 pl-5.5 leading-snug">{{ $permMeta['desc'] }}</p>

                                            {{-- Toggle Izin Tulis / Maker-Checker --}}
                                            <div class="mt-2 pt-1.5 border-t border-slate-100 pl-5.5 flex items-center justify-between text-[11px]">
                                                <label class="flex items-center gap-1.5 cursor-pointer text-slate-600 select-none">
                                                    <input type="checkbox"
                                                           name="write_{{ $permKey }}"
                                                           value="1"
                                                           @checked($canWrite)
                                                           class="rounded text-amber-600 focus:ring-amber-500 w-3 h-3">
                                                    <span class="text-[11px]">Izin Tulis / Ubah</span>
                                                </label>
                                                <span class="text-[10px] font-semibold {{ $canWrite ? 'text-amber-600' : ($hasAccess ? 'text-blue-600' : 'text-slate-400') }}">
                                                    {{ $canWrite ? 'Full Write' : ($hasAccess ? 'Read Only' : 'No Access') }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </form>
        </div>
    @endforeach
</div>

@push('modals')
{{-- Modal Tambah Role Baru --}}
<div id="createRoleModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 sm:p-6 overflow-y-auto" aria-labelledby="modal-title-create" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeCreateRoleModal()"></div>

    <div class="relative w-full max-w-lg my-auto bg-white rounded-2xl text-left shadow-2xl border border-slate-200 overflow-hidden z-10">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-[#114E84]/10 text-[#114E84] flex items-center justify-center">
                    @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-[#114E84]'])
                </div>
                <div>
                    <h3 class="font-bold text-ink text-base" id="modal-title-create">Tambah Role / Peran Baru</h3>
                    <p class="text-xs text-slate-500">Definisikan peran baru untuk operasional sistem perbankan</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateRoleModal()" class="text-slate-400 hover:text-slate-600 transition p-1.5 rounded-lg hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.roles.store') }}" class="p-5 sm:p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Peran / Label <span class="text-rose-500">*</span></label>
                <input type="text"
                       name="label"
                       id="create_role_label"
                       required
                       placeholder="Contoh: Staf Kepatuhan & Manajemen Risiko"
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition"
                       oninput="autoGenerateSlug(this.value, 'create_role_nama')">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kode / Slug Sistem <span class="text-rose-500">*</span></label>
                <input type="text"
                       name="nama"
                       id="create_role_nama"
                       required
                       placeholder="Contoh: staf_kepatuhan"
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-mono text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
                <p class="text-[11px] text-slate-400 mt-1">Gunakan huruf kecil dan garis bawah (contoh: staf_audit, unit_khusus).</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Wewenang &amp; Tanggung Jawab</label>
                <textarea name="deskripsi"
                          rows="3"
                          placeholder="Penjelasan ringkas tanggung jawab dan wewenang peran ini di sistem..."
                          class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition resize-none"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeCreateRoleModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#114E84] text-white text-xs font-bold hover:bg-[#0E4272] transition shadow-xs flex items-center gap-1.5">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-amber-300'])
                    Simpan Role Baru
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Role --}}
<div id="editRoleModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 sm:p-6 overflow-y-auto" aria-labelledby="modal-title-edit" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeEditRoleModal()"></div>

    <div class="relative w-full max-w-lg my-auto bg-white rounded-2xl text-left shadow-2xl border border-slate-200 overflow-hidden z-10">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-700 flex items-center justify-center">
                    @include('partials.icon', ['name' => 'pencil', 'class' => 'w-4 h-4 text-amber-600'])
                </div>
                <div>
                    <h3 class="font-bold text-ink text-base" id="modal-title-edit">Edit Data Peran (Role)</h3>
                    <p class="text-xs text-slate-500">Perbarui nama tampilan, deskripsi, atau rincian wewenang</p>
                </div>
            </div>
            <button type="button" onclick="closeEditRoleModal()" class="text-slate-400 hover:text-slate-600 transition p-1.5 rounded-lg hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="editRoleForm" method="POST" action="" class="p-5 sm:p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Peran / Label <span class="text-rose-500">*</span></label>
                <input type="text"
                       name="label"
                       id="edit_role_label"
                       required
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kode / Slug Sistem</label>
                <input type="text"
                       name="nama"
                       id="edit_role_nama"
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-mono text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
                <p id="edit_role_slug_hint" class="text-[11px] text-slate-400 mt-1">Gunakan huruf kecil dan garis bawah.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Wewenang &amp; Tanggung Jawab</label>
                <textarea name="deskripsi"
                          id="edit_role_deskripsi"
                          rows="3"
                          placeholder="Penjelasan ringkas tanggung jawab dan wewenang peran ini di sistem..."
                          class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition resize-none"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeEditRoleModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#114E84] text-white text-xs font-bold hover:bg-[#0E4272] transition shadow-xs flex items-center gap-1.5">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-amber-300'])
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

<script>
    function openCreateRoleModal() {
        const modal = document.getElementById('createRoleModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            const input = document.getElementById('create_role_label');
            if (input) input.focus();
        }, 50);
    }

    function closeCreateRoleModal() {
        const modal = document.getElementById('createRoleModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function openEditRoleModal(role) {
        const modal = document.getElementById('editRoleModal');
        const form = document.getElementById('editRoleForm');
        form.action = "{{ url('admin/roles') }}/" + role.id;

        document.getElementById('edit_role_label').value = role.label || '';
        document.getElementById('edit_role_deskripsi').value = role.deskripsi || '';

        const slugInput = document.getElementById('edit_role_nama');
        const slugHint = document.getElementById('edit_role_slug_hint');

        slugInput.value = role.nama || '';

        if (role.is_system) {
            slugInput.readOnly = true;
            slugInput.classList.add('bg-slate-100', 'cursor-not-allowed', 'text-slate-500');
            slugHint.textContent = 'Slug kode peran bawaan sistem dilindungi untuk integritas otorisasi aplikasi.';
            slugHint.classList.add('text-amber-600');
            slugHint.classList.remove('text-slate-400');
        } else {
            slugInput.readOnly = false;
            slugInput.classList.remove('bg-slate-100', 'cursor-not-allowed', 'text-slate-500');
            slugHint.textContent = 'Gunakan huruf kecil dan garis bawah (contoh: staf_audit, unit_khusus).';
            slugHint.classList.remove('text-amber-600');
            slugHint.classList.add('text-slate-400');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeEditRoleModal() {
        const modal = document.getElementById('editRoleModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function autoGenerateSlug(val, targetId) {
        const slugInput = document.getElementById(targetId);
        if (!slugInput || slugInput.readOnly) return;
        const slug = val.toLowerCase()
                        .trim()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/[\s_-]+/g, '_')
                        .replace(/^-+|-+$/g, '');
        slugInput.value = slug;
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCreateRoleModal();
            closeEditRoleModal();
        }
    });
</script>
@endsection

