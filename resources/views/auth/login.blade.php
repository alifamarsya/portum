<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — PORTUM | Bank Sulteng</title>
<style>
:root{--navy:#07366f;--gold:#dca51b;--text:#10284a;--muted:#71809a;--line:#d9e0e9}
*{box-sizing:border-box}body{margin:0;min-height:100vh;font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif;background:#f5f8fc;color:var(--text)}
.login-page{min-height:100vh;padding:18px;display:flex;align-items:stretch;justify-content:center}
.login-shell{width:min(1400px,100%);min-height:calc(100vh - 36px);display:grid;grid-template-columns:56% 44%;overflow:hidden;border-radius:24px;background:#fff;box-shadow:0 18px 55px rgba(20,46,82,.12)}
.brand-panel {
    position: relative;
    min-height: 720px;
    overflow: hidden;
    padding: 42px 64px 34px;

    display: flex;
    flex-direction: column;

    color: #fff;

    background:
        linear-gradient(
            180deg,
            rgba(11, 57, 111, .30) 0%,
            rgba(6, 45, 91, .72) 58%,
            rgba(4, 38, 79, .98) 100%
        ),
        url("{{ asset('images/bank-sulteng-building.png') }}")
        center center / cover no-repeat;
}
.brand-content,.brand-footer{position:relative;z-index:2}.brand-logo{width:min(255px,48%);height:auto;display:block;filter:drop-shadow(0 2px 8px rgba(0,0,0,.08))}
.brand-copy{max-width:510px;margin-top:auto;padding-bottom:24px}.brand-copy h1{margin:0 0 8px;font-size:clamp(42px,4vw,58px);line-height:1;font-weight:800;letter-spacing:.02em}.subtitle{margin:0 0 22px;color:#f2b92c;font-size:clamp(20px,2vw,27px);font-weight:650}.description{margin:0;max-width:500px;font-size:17px;line-height:1.65;color:rgba(255,255,255,.94)}
.security-note{display:flex;align-items:center;gap:15px;margin-top:48px;max-width:480px}.security-icon{width:48px;height:48px;flex:0 0 48px;display:grid;place-items:center;border:2px solid #f0b72a;border-radius:50%;color:#f0b72a;font-size:21px}.security-note p{margin:0;color:#fff;font-size:15px;line-height:1.55}.brand-footer{margin-top:auto;padding-top:20px;font-size:13px;color:rgba(255,255,255,.85);text-align:center}
.form-panel{display:flex;align-items:center;justify-content:center;padding:52px 8%;background:#fff}.login-card{width:min(500px,100%)}.login-card h2{margin:0;color:var(--navy);font-size:clamp(32px,3vw,43px);line-height:1.12;font-weight:800}.login-intro{margin:12px 0 40px;color:var(--muted);font-size:16px;line-height:1.5}
.alert{margin-bottom:22px;padding:13px 15px;border:1px solid #f1b7b7;border-radius:10px;background:#fff5f5;color:#9c2c2c;font-size:14px}.field{margin-bottom:23px}.field label{display:block;margin-bottom:9px;font-size:15px;font-weight:700}.input-wrap{position:relative}.input-icon{position:absolute;left:17px;top:50%;transform:translateY(-50%);width:21px;height:21px;color:#6d7c92;pointer-events:none}.form-input{width:100%;height:56px;padding:0 50px 0 51px;border:1px solid var(--line);border-radius:10px;outline:none;background:#fff;color:var(--text);font-size:15px;transition:.2s}.form-input:focus{border-color:#2b6fb5;box-shadow:0 0 0 4px rgba(43,111,181,.11)}.form-input::placeholder{color:#8c98aa}
.password-toggle{position:absolute;right:14px;top:50%;transform:translateY(-50%);border:0;background:transparent;color:#64748b;cursor:pointer;padding:5px}.password-toggle svg{width:21px;height:21px}.login-options{margin:2px 0 30px;display:flex;align-items:center}.remember{display:inline-flex;align-items:center;gap:10px;font-size:14px;cursor:pointer}.remember input{width:19px;height:19px;accent-color:var(--navy)}
.login-button{width:100%;height:56px;border:0;border-radius:10px;background:linear-gradient(180deg,#0a407f,#07366f);color:#fff;font-size:16px;font-weight:800;letter-spacing:.04em;cursor:pointer;box-shadow:0 8px 18px rgba(7,54,111,.18)}.login-button:hover{filter:brightness(1.06)}
.admin-help{margin-top:28px;text-align:center;color:#66758b;font-size:14px}.admin-help a{color:#0758a4;font-weight:700;text-decoration:none}
@media(max-width:980px){.login-shell{grid-template-columns:1fr}.brand-panel{min-height:440px;padding:32px 38px}.brand-copy{margin-top:70px}.brand-logo{width:220px}.form-panel{padding:55px 38px}}
@media(max-width:600px){.login-page{padding:0}.login-shell{min-height:100vh;border-radius:0}.brand-panel{min-height:365px;padding:28px 25px}.brand-logo{width:190px}.brand-copy{margin-top:52px}.brand-copy h1{font-size:38px}.subtitle{font-size:19px}.description,.security-note,.brand-footer{display:none}.form-panel{padding:42px 25px 50px;align-items:flex-start}.login-card h2{font-size:31px}}
</style>
</head>
<body>
<div class="login-page">
<main class="login-shell">
<section class="brand-panel">
<div class="brand-content">
<img class="brand-logo" src="{{ asset('images/bank-sulteng.png') }}" alt="Bank Sulteng">
<div class="brand-copy">
<h1>PORTUM</h1>
<p class="subtitle">Portal Umum &amp; Aset</p>
<p class="description">Sistem terintegrasi untuk pengelolaan data Umum, Aset, dan Pengadaan secara efisien dan transparan.</p>
<div class="security-note"><div class="security-icon">⌾</div><p><strong>Akses terbatas untuk pengguna terdaftar</strong><br>Terenkripsi &amp; diaudit menyeluruh.</p></div>
</div>
</div>
<div class="brand-footer">© {{ date('Y') }} PT Bank Sulteng. All rights reserved.</div>
</section>

<section class="form-panel">
<div class="login-card">
<h2>Selamat Datang</h2>
<p class="login-intro">Masuk untuk melanjutkan ke Portal Umum &amp; Aset.</p>

@if ($errors->any())
<div class="alert" role="alert">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('login') }}">
@csrf
<div class="field">
<label for="username">Username</label>
<div class="input-wrap">
<svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c.8-3.2 3.1-5 7-5s6.2 1.8 7 5"/></svg>
<input id="username" name="username" type="text" class="form-input" value="{{ old('username') }}" placeholder="Masukkan username Anda" autocomplete="username" autofocus required>
</div>
</div>

<div class="field">
<label for="password">Password</label>
<div class="input-wrap">
<svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
<input id="password" name="password" type="password" class="form-input" placeholder="Masukkan password Anda" autocomplete="current-password" required>
<button type="button" class="password-toggle" id="togglePassword" aria-label="Tampilkan password">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
</button>
</div>
</div>

<div class="login-options"><label class="remember"><input type="checkbox" name="remember" value="1"><span>Ingat saya</span></label></div>
<button type="submit" class="login-button">MASUK</button>
</form>

<div class="admin-help">Belum memiliki akun? <a href="mailto:administrator@banksulteng.co.id">Hubungi administrator.</a></div>
</div>
</section>
</main>
</div>
<script>
const passwordInput=document.getElementById('password'),toggle=document.getElementById('togglePassword');
toggle?.addEventListener('click',function(){const show=passwordInput.type==='password';passwordInput.type=show?'text':'password';this.setAttribute('aria-label',show?'Sembunyikan password':'Tampilkan password');});
</script>
</body>
</html>
