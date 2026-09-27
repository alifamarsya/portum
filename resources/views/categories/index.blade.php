@extends('layouts.app')
@section('title', 'Manajemen Kategori Tiket')

@section('content')
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-[12px] font-semibold uppercase tracking-wider text-gold mb-0.5">Konfigurasi Helpdesk</p>
                <h1 class="text-2xl font-bold text-ink">Manajemen Kategori Tiket &amp; SLA</h1>
                <p class="text-xs text-slate-500 mt-1">Kelola daftar kategori dinamis, target jam penyelesaian (SLA Resolution), dan pemetaan ke Bagian terkait.</p>
            </div>
            <div>
                <button type="button" onclick="openCreateModal()"
                        class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow transition">
                    @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-white'])
                    Tambah Kategori Baru
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Overview --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-card">
            <span class="text-xs text-slate-400 block font-medium">Total Kategori</span>
            <span class="text-2xl font-bold text-ink font-mono mt-1 block">{{ $stats['total'] }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-card">
            <span class="text-xs text-slate-400 block font-medium">Kategori Permintaan</span>
            <span class="text-2xl font-bold text-sky-700 font-mono mt-1 block">{{ $stats['permintaan'] }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-card">
            <span class="text-xs text-slate-400 block font-medium">Kategori Permasalahan</span>
            <span class="text-2xl font-bold text-orange-600 font-mono mt-1 block">{{ $stats['permasalahan'] }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-card">
            <span class="text-xs text-slate-400 block font-medium">Kategori Aktif</span>
            <span class="text-2xl font-bold text-emerald-600 font-mono mt-1 block">{{ $stats['aktif'] }}</span>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-card p-4 mb-6">
        <form method="GET" action="{{ route('ticket-categories.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama kategori..."
                       class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-xs focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84]">
            </div>
            <div>
                <select name="jenis_pengajuan" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs focus:border-[#114E84]">
                    <option value="">-- Semua Jenis Pengajuan --</option>
                    <option value="Permintaan" {{ request('jenis_pengajuan') === 'Permintaan' ? 'selected' : '' }}>Permintaan</option>
                    <option value="Permasalahan" {{ request('jenis_pengajuan') === 'Permasalahan' ? 'selected' : '' }}>Permasalahan</option>
                </select>
            </div>
            <div>
                <select name="department_id" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs focus:border-[#114E84]">
                    <option value="">-- Semua Bagian --</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold py-2 px-3 rounded-xl transition">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'jenis_pengajuan', 'department_id']))
                    <a href="{{ route('ticket-categories.index') }}" class="text-xs text-slate-500 hover:text-slate-800 px-2 py-2">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Categories Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Nama Kategori</th>
                        <th class="py-3.5 px-4">Jenis Pengajuan</th>
                        <th class="py-3.5 px-4">Target SLA Resolusi</th>
                        <th class="py-3.5 px-4">Bagian Terkait</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($categories as $cat)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-ink text-sm block">{{ $cat->name }}</span>
                                <span class="text-[11px] text-slate-400">Urutan: {{ $cat->sort_order }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($cat->jenis_pengajuan === 'Permasalahan')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-orange-50 text-orange-700 border border-orange-200">
                                        Permasalahan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                        Permintaan
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl font-mono font-bold text-xs bg-amber-50 text-amber-800 border border-amber-200">
                                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-3.5 h-3.5 text-amber-600'])
                                    {{ $cat->sla_resolution_hours }} Jam Kerja
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($cat->department)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#114E84]">
                                        <span class="w-2 h-2 rounded-full bg-[#114E84]"></span>
                                        {{ $cat->department->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Umum / Semua Bagian</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <form method="POST" action="{{ route('ticket-categories.toggle', $cat) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="Klik untuk mengubah status aktif"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold transition {{ $cat->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $cat->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $cat->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button"
                                            onclick='openEditModal(@json($cat))'
                                            class="p-1.5 text-slate-500 hover:text-[#114E84] hover:bg-blue-50 rounded-lg transition" title="Edit Kategori">
                                        @include('partials.icon', ['name' => 'edit', 'class' => 'w-4 h-4'])
                                    </button>

                                    <form method="POST" action="{{ route('ticket-categories.destroy', $cat) }}"
                                          onsubmit="return confirm('Yakin ingin menghapus kategori {{ $cat->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Kategori">
                                            @include('partials.icon', ['name' => 'trash', 'class' => 'w-4 h-4'])
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                Tidak ada kategori tiket yang sesuai kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL TAMBAH KATEGORI --}}
    <div id="modal-create" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-ink">Tambah Kategori Tiket Baru</h3>
                <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('ticket-categories.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Pengajuan <span class="text-rose-500">*</span></label>
                    <select name="jenis_pengajuan" required class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-xs focus:border-[#114E84]">
                        <option value="Permintaan">Permintaan</option>
                        <option value="Permasalahan">Permasalahan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kategori <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Perbaikan AC Ruang Server"
                           class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-xs focus:border-[#114E84]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">SLA Resolusi (Jam) <span class="text-rose-500">*</span></label>
                        <input type="number" name="sla_resolution_hours" required min="1" max="720" value="24"
                               class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-xs focus:border-[#114E84]">
                        <p class="text-[10px] text-slate-400 mt-1">Target jam pengerjaan (1 - 720 jam).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Bagian Terkait</label>
                        <select name="department_id" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs focus:border-[#114E84]">
                            <option value="">-- Bebas / Belum Dipetakan --</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 items-center pt-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Urut Tampil</label>
                        <input type="number" name="sort_order" value="0" min="0" class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-xs">
                    </div>

                    <div class="pt-4">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-[#114E84]">
                            <span class="text-xs font-semibold text-slate-700">Status Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeCreateModal()" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800">Batal</button>
                    <button type="submit" class="bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold px-5 py-2 rounded-xl shadow">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT KATEGORI --}}
    <div id="modal-edit" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-ink">Edit Kategori Tiket</h3>
                <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
            </div>

            <form id="form-edit" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Pengajuan <span class="text-rose-500">*</span></label>
                    <select id="edit-jenis" name="jenis_pengajuan" required class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-xs focus:border-[#114E84]">
                        <option value="Permintaan">Permintaan</option>
                        <option value="Permasalahan">Permasalahan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kategori <span class="text-rose-500">*</span></label>
                    <input type="text" id="edit-name" name="name" required
                           class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-xs focus:border-[#114E84]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">SLA Resolusi (Jam) <span class="text-rose-500">*</span></label>
                        <input type="number" id="edit-sla" name="sla_resolution_hours" required min="1" max="720"
                               class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-xs focus:border-[#114E84]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Bagian Terkait</label>
                        <select id="edit-department" name="department_id" class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs focus:border-[#114E84]">
                            <option value="">-- Bebas / Belum Dipetakan --</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 items-center pt-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Urut Tampil</label>
                        <input type="number" id="edit-sort" name="sort_order" min="0" class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-xs">
                    </div>

                    <div class="pt-4">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="edit-active" name="is_active" value="1" class="rounded border-slate-300 text-[#114E84]">
                            <span class="text-xs font-semibold text-slate-700">Status Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800">Batal</button>
                    <button type="submit" class="bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold px-5 py-2 rounded-xl shadow">Perbarui Kategori</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('modal-create').classList.remove('hidden');
        }
        function closeCreateModal() {
            document.getElementById('modal-create').classList.add('hidden');
        }

        function openEditModal(cat) {
            const form = document.getElementById('form-edit');
            form.action = '/kategori-tiket/' + cat.id;
            document.getElementById('edit-jenis').value = cat.jenis_pengajuan;
            document.getElementById('edit-name').value = cat.name;
            document.getElementById('edit-sla').value = cat.sla_resolution_hours;
            document.getElementById('edit-department').value = cat.department_id || '';
            document.getElementById('edit-sort').value = cat.sort_order || 0;
            document.getElementById('edit-active').checked = !!cat.is_active;

            document.getElementById('modal-edit').classList.remove('hidden');
        }
        function closeEditModal() {
            document.getElementById('modal-edit').classList.add('hidden');
        }
    </script>
@endsection
