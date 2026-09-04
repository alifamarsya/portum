<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          brand: {
            DEFAULT: '#0E1726',
            light: '#1B2A43',
            dark: '#070C15',
            surface: '#131F33',
            accent: '#2563EB',
          },
          gold: {
            DEFAULT: '#D4A038',
            soft: '#F4E9D0',
            light: '#FCF8F0',
            dark: '#AD7B1C',
          },
          canvas: '#F8FAFC',
          ink: '#0F172A',
          muted: '#64748B',
        },
        boxShadow: {
          subtle: '0 1px 2px 0 rgba(0, 0, 0, 0.03)',
          card: '0 1px 3px 0 rgba(15, 23, 42, 0.04), 0 1px 2px -1px rgba(15, 23, 42, 0.03)',
          hover: '0 10px 25px -5px rgba(15, 23, 42, 0.06), 0 8px 10px -6px rgba(15, 23, 42, 0.04)',
          glow: '0 0 20px -3px rgba(212, 160, 56, 0.25)',
          sidebar: '4px 0 24px 0 rgba(15, 23, 42, 0.04)',
        },
      },
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'Manrope', 'ui-sans-serif', 'system-ui'],
        mono: ['"IBM Plex Mono"', 'ui-monospace', 'monospace'],
      },
    },
  }
</script>
<style>
  body { font-family: 'Plus Jakarta Sans', Manrope, ui-sans-serif, system-ui; }
  .font-mono { font-family: 'IBM Plex Mono', ui-monospace, monospace; }

  ::-webkit-scrollbar { width: 6px; height: 6px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
  ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

  aside ::-webkit-scrollbar { width: 4px; }
  aside ::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.12); border-radius: 999px; }
  aside ::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.25); }

  ::selection { background: #0E1726; color: #D4A038; }

  a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible {
    outline: 2px solid #D4A038; outline-offset: 2px; border-radius: 8px;
  }

  input[type="checkbox"], input[type="radio"] { accent-color: #0E1726; }

  @keyframes fadeInUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
  .animate-enter { animation: fadeInUp .3s cubic-bezier(0.16, 1, 0.3, 1) both; }

  details > summary { list-style: none; }
  details > summary::-webkit-details-marker { display: none; }
  details[open] .portum-chevron { transform: rotate(90deg); }

  /* Glass backdrop effect matching canvas */
  .glass-header {
    background: rgba(248, 250, 252, 0.92);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
  }
</style>
