<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Portum</title>
    @include('partials.head-assets')
</head>
<body class="bg-canvas text-ink antialiased">
<div class="min-h-screen flex">

    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-brand to-brand-dark text-white flex-col justify-between p-12 relative overflow-hidden">
        <div class="absolute inset-0 opacity-[0.06]"
             style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 22px 22px;"></div>

        <div class="flex items-center gap-3 relative">
            @include('partials.brand-mark', ['class' => 'w-9 h-9'])
            <span class="font-bold text-lg tracking-tight">Portum</span>
        </div>

        <div class="max-w-sm relative">
            <p class="text-[13px] uppercase tracking-widest text-gold font-semibold mb-3">Portal Umum &amp; Aset</p>
            <h1 class="text-3xl font-bold leading-tight mb-4">Satu pintu untuk Umum, Aset, dan Pengadaan.</h1>
            <p class="text-slate-300 text-sm leading-relaxed">
                Setiap perubahan pada transaksi tercatat dalam rantai audit yang saling terhubung &mdash;
                setiap baris mengunci baris sebelumnya, sehingga jejaknya tidak bisa diubah diam-diam.
            </p>
            <div class="flex items-center gap-2 mt-6 text-[12.5px] text-slate-300">
                @include('partials.icon', ['name' => 'shield', 'class' => 'w-4 h-4 text-gold flex-shrink-0'])
                Terenkripsi &amp; diaudit menyeluruh
            </div>
        </div>

        <p class="text-[11.5px] font-mono text-slate-500 relative">Divisi Umum &middot; PT Bank Sulteng</p>

        <div class="absolute -right-16 -bottom-16 w-72 h-72 rounded-full border border-white/10"></div>
        <div class="absolute -right-6 -bottom-32 w-72 h-72 rounded-full border border-white/10"></div>
    </div>

    <div class="flex-1 flex items-center justify-center p-8">
        <div class="w-full max-w-sm">
            <div class="lg:hidden flex items-center gap-2.5 mb-8">
                @include('partials.brand-mark', ['class' => 'w-8 h-8 text-brand'])
                <span class="font-bold text-lg text-brand">Portum</span>
            </div>

            <h2 class="text-xl font-bold mb-1">Masuk ke akun Anda</h2>
            <p class="text-sm text-slate-500 mb-6">Gunakan username dan password yang diberikan admin.</p>

            @if ($errors->any())
                <div class="mb-4 flex items-start gap-2.5 rounded-xl bg-red-50 border border-red-200 text-red-700 px-3.5 py-2.5 text-sm">
                    @include('partials.icon', ['name' => 'alert', 'class' => 'w-4 h-4 flex-shrink-0 mt-0.5', 'stroke' => 2])
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[13px] font-medium mb-1.5 text-slate-700">Username</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            @include('partials.icon', ['name' => 'users', 'class' => 'w-4 h-4'])
                        </span>
                        <input type="text" name="username" value="{{ old('username') }}" required autofocus
                               class="w-full border border-slate-300 rounded-lg pl-9 pr-3.5 py-2.5 text-sm bg-white focus:border-brand focus:ring-1 focus:ring-brand transition">
                    </div>
                </div>
                <div>
                    <label class="block text-[13px] font-medium mb-1.5 text-slate-700">Password</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            @include('partials.icon', ['name' => 'lock', 'class' => 'w-4 h-4'])
                        </span>
                        <input type="password" name="password" required
                               class="w-full border border-slate-300 rounded-lg pl-9 pr-3.5 py-2.5 text-sm bg-white focus:border-brand focus:ring-1 focus:ring-brand transition">
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300"> Ingat saya
                </label>
                <button class="w-full bg-brand text-white rounded-lg py-2.5 text-sm font-semibold hover:bg-brand-light transition shadow-brand">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
