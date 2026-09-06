@extends('layouts.app')
@section('title', 'Peran & Hak Akses')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 mb-1.5">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#114E84]/10 text-[#114E84] border border-[#114E84]/20">
            @include('partials.icon', ['name' => 'shield', 'class' => 'w-3 h-3 text-[#114E84]'])
            RBAC Management
        </span>
        <span class="text-slate-300">•</span>
        <span class="text-xs text-slate-500 font-medium">7 Role Standar Bank Sulteng</span>
    </div>
    <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">
        Peran &amp; Hak Akses (Role Permission)
    </h1>
    <p class="text-xs sm:text-sm text-slate-500 mt-1">
        Atur matriks perizinan modul dan hak akses baca/tulis untuk 7 peran di sistem Portum Bank Sulteng.
    </p>
</div>

{{-- Kebijakan Role Info Banner --}}
<div class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-blue-50/80 to-indigo-50/80 border border-blue-200/80 flex items-start gap-3.5 text-xs text-slate-700">
    <div class="w-8 h-8 rounded-xl bg-[#114E84] text-white flex items-center justify-center flex-shrink-0 mt-0.5">
        @include('partials.icon', ['name' => 'shield', 'class' => 'w-4 h-4 text-amber-300'])
    </div>
    <div class="space-y-1 leading-relaxed">
        <p class="font-bold text-ink text-sm">Ketentuan Struktur 7 Role &amp; Kapasitas Akun:</p>
        <p>
            • <strong>Role User (Pemohon):</strong> Bersifat <em>multi-user</em> dan dapat memiliki banyak akun untuk digunakan oleh staf dari berbagai kantor cabang dan divisi pemohon tiket layanan.<br>
            • <strong>6 Role Lainnya:</strong> (Admin, Pimpinan Divisi, Operator, Staf Umum &amp; RT, Staf Aset/Inventaris &amp; Logistik, Staf Pengadaan serta Pemeliharaan) bertindak sebagai akun operasional tunggal (1 role = 1 user penanggung jawab).
        </p>
    </div>
</div>

<div class="space-y-6">
    @foreach ($roles as $role)
        @php
            $isMulti = $role->nama === 'user';
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
                            @if ($isMulti)
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-50 text-[#114E84] border border-blue-200">
                                    Multi-User ({{ $userCount }} Akun Terdaftar)
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    1 User Tunggal ({{ $assignedUsers ?: 'Belum Ada User' }})
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
@endsection
