<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          brand: {
            DEFAULT: '#16233D',
            light: '#223257',
            dark: '#0D1526',
          },
          gold: {
            DEFAULT: '#BF8F3D',
            soft: '#E9D9B6',
            light: '#F4E9D2',
          },
          canvas: '#F5F3EE',
          ink: '#1C2333',
        },
        boxShadow: {
          card: '0 1px 2px rgba(16, 24, 39, 0.04), 0 1px 12px rgba(16, 24, 39, 0.05)',
          hover: '0 4px 10px rgba(16, 24, 39, 0.06), 0 8px 30px rgba(16, 24, 39, 0.08)',
          brand: '0 8px 24px rgba(22, 35, 61, 0.18)',
          gold: '0 8px 20px rgba(191, 143, 61, 0.22)',
        },
      },
      fontFamily: {
        sans: ['Manrope', 'ui-sans-serif', 'system-ui'],
        mono: ['"IBM Plex Mono"', 'ui-monospace', 'monospace'],
      },
    },
  }
</script>
<style>
  body { font-family: 'Manrope', ui-sans-serif, system-ui; }
  .font-mono { font-family: 'IBM Plex Mono', ui-monospace, monospace; }

  ::-webkit-scrollbar { width: 7px; height: 7px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: #d8d3c8; border-radius: 999px; }
  ::-webkit-scrollbar-thumb:hover { background: #c7c0b0; }

  aside ::-webkit-scrollbar-thumb { background: rgba(255,255,255,.14); }
  aside ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.22); }

  ::selection { background: #BF8F3D; color: #fff; }

  a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
    outline: 2px solid #BF8F3D; outline-offset: 2px; border-radius: 6px;
  }

  input[type="checkbox"], input[type="radio"] { accent-color: #16233D; }

  @keyframes fadeInUp { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
  .animate-enter { animation: fadeInUp .35s ease both; }

  details > summary { list-style: none; }
  details > summary::-webkit-details-marker { display: none; }
  details[open] .chev { transform: rotate(180deg); }

  .nav-link { position: relative; }
  .nav-link.active::before {
    content: ''; position: absolute; left: -12px; top: 50%; transform: translateY(-50%);
    width: 3px; height: 16px; border-radius: 999px; background: #BF8F3D;
  }
</style>
