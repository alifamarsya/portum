<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ganti Password — Bank Sulteng</title>
    @include('partials.head-assets')
</head>
<body class="bg-canvas text-ink antialiased min-h-screen flex items-center justify-center p-6">
    <div class="absolute inset-0 opacity-[0.35] pointer-events-none"
         style="background-image: radial-gradient(circle, #ddd6c8 1px, transparent 1px); background-size: 24px 24px;"></div>

    <div class="w-full max-w-sm bg-white rounded-2xl shadow-hover border border-slate-200 p-8 relative">
        <div class="flex items-center gap-2.5 mb-6">
            <img src="{{ asset('images/bank-sulteng.png') }}" alt="Bank Sulteng" class="h-6 w-auto">
        </div>

        <div class="w-11 h-11 rounded-xl bg-gold-light text-gold flex items-center justify-center mb-4">
            @include('partials.icon', ['name' => 'key', 'class' => 'w-5 h-5'])
        </div>

        <h1 class="text-lg font-bold mb-1">Ganti password Anda</h1>
        <p class="text-sm text-slate-500 mb-6">Ini login pertama Anda &mdash; buat password baru sebelum melanjutkan.</p>

        @if ($errors->any())
            <div class="mb-4 flex items-start gap-2.5 rounded-xl bg-red-50 border border-red-200 text-red-700 px-3.5 py-2.5 text-sm">
                @include('partials.icon', ['name' => 'alert', 'class' => 'w-4 h-4 flex-shrink-0 mt-0.5', 'stroke' => 2])
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('password.force-change.submit') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[13px] font-medium mb-1.5 text-slate-700">Password Baru</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
            </div>
            <div>
                <label class="block text-[13px] font-medium mb-1.5 text-slate-700">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required minlength="8"
                       class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:border-brand focus:ring-1 focus:ring-brand transition">
            </div>
            <button class="w-full bg-brand text-white rounded-lg py-2.5 text-sm font-semibold hover:bg-brand-light transition shadow-brand">Simpan &amp; Lanjutkan</button>
        </form>
    </div>
</body>
</html>
