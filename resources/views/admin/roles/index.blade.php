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
            Atur matriks perizinan modul dan hak akses baca/tulis seluruh peran operasional sistem Portum.
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
            $isMutlak = in_array($role->nama, ['admin', 'pimpinan', 'kabag_umum', 'kabag_aset', 'kabag_pengadaan']);
            $userCount = $role->users->count();
            $assignedUsers = $role->users->pluck('username')->implode(', ');
        @endphp
        <form method="POST" action="{{ route('admin.roles.permissions', $role) }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-5 sm:p-6 transition-all hover:border-[#114E84]/30">
            @csrf
            {{-- Card Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 mb-5 border-b border-slate-100">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#114E84]/10 text-[#114E84] flex items-center justify-center flex-shrink-0 font-bold text-sm">
                        {{ $loop->iteration }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-base font-bold text-ink">{{ $role->label }}</h2>
                            <span class="text-xs font-mono text-slate-400">({{ $role->nama }})</span>
                            @if ($isMutlak)
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    Role Mutlak ({{ $assignedUsers ?: '1 User' }})
                                </span>
                            @elseif ($role->nama === 'user')
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-50 text-[#114E84] border border-blue-200">
                                    Multi-User ({{ $userCount }} Akun Terdaftar)
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Multi-User ({{ $userCount }} Akun Terdaftar)
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $role->deskripsi }}</p>
                    </div>
                </div>

                <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold shadow-xs hover:shadow transition flex-shrink-0">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-amber-300'])
                    <span>Simpan Hak Akses</span>
                </button>
            </div>

            {{-- Grouped Permissions Grid --}}
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
                                            <label class="flex items-center gap-1.5 cursor-pointer text-slate-600">
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
    @endforeach
</div>

@push('modals')
{{-- Modal Tambah Role Baru --}}
<div id="createRoleModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 sm:p-6 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeCreateRoleModal()"></div>

    <div class="relative w-full max-w-lg my-auto bg-white rounded-2xl text-left shadow-2xl border border-slate-200 overflow-hidden z-10">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-[#114E84]/10 text-[#114E84] flex items-center justify-center">
                    @include('partials.icon', ['name' => 'shield', 'class' => 'w-4 h-4 text-[#114E84]'])
                </div>
                <div>
                    <h3 class="font-bold text-ink text-base" id="modal-title">Tambah Role / Peran Baru</h3>
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
                       id="role_label_input"
                       required
                       placeholder="Contoh: Kepala Bagian Operasional"
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition"
                       oninput="autoGenerateSlug(this.value)">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kode / Slug Sistem <span class="text-rose-500">*</span></label>
                <input type="text"
                       name="nama"
                       id="role_nama_input"
                       required
                       placeholder="Contoh: kepala_bagian"
                       class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-mono text-ink focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
                <p class="text-[11px] text-slate-400 mt-1">Gunakan huruf kecil dan garis bawah (contoh: staf_audit, kepala_bagian).</p>
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
@endpush

<script>
    function openCreateRoleModal() {
        const modal = document.getElementById('createRoleModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            const input = document.getElementById('role_label_input');
            if (input) input.focus();
        }, 50);
    }

    function closeCreateRoleModal() {
        const modal = document.getElementById('createRoleModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function autoGenerateSlug(val) {
        const slugInput = document.getElementById('role_nama_input');
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
        }
    });
</script>
@endsection
