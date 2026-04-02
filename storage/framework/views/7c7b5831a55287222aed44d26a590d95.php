<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="description" content="SIMANTAB - Sistem Audit Internal Terintegrasi Inspektorat Kabupaten Rembang"/>
<title>Inspektorat Kabupaten Rembang — SIMANTAB</title>

<!-- Preconnect for performance -->
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>

<!-- Favicon -->
<link rel="icon" type="image/png" href="https://insp.rembangkab.go.id/assets/images/logo.png"/>

<!-- Fonts: Replaced Inter with Sora (display) + DM Sans (body) for distinction -->
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>

<!-- Material Icons (subset only) -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet"/>

<!-- Tailwind CDN -->
<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>

<script>
  tailwind.config = {
    darkMode: "class",
    theme: {
      extend: {
        colors: {
          primary: "#003366",
          secondary: "#c5a059",
          "bg-light": "#f8fafc",
          "bg-dark": "#020617",
          "card-light": "#ffffff",
          "card-dark": "#0f172a",
          "border-light": "#e2e8f0",
          "border-dark": "#1e293b",
        },
        fontFamily: {
          display: ["Sora", "sans-serif"],
          sans: ["DM Sans", "sans-serif"],
        },
      },
    },
  };
</script>

<style>
  /* =============================================
     GLOBAL RESET & BASE
  ============================================= */
  *, *::before, *::after { box-sizing: border-box; }
  html { -webkit-font-smoothing: antialiased; }

  /* =============================================
     MARQUEE ANIMATION
  ============================================= */
  @keyframes marquee {
    from { transform: translateX(0); }
    to   { transform: translateX(-50%); }
  }
.marquee-track {
  display: inline-flex;  /* bukan flex — ikuti lebar konten, bukan stretch */
  white-space: nowrap;
  animation: marquee 30s linear infinite;
  will-change: transform;
}
  .marquee-track:hover { animation-play-state: paused; }

  /* =============================================
     TIMELINE CONNECTOR LINE
  ============================================= */
  .timeline::before {
    content: '';
    position: absolute;
    inset: 0;
    left: 1.25rem;
    width: 2px;
    background: linear-gradient(to bottom, transparent, #e2e8f0, transparent);
    transform: translateX(-50%);
  }
  @media (min-width: 768px) {
    .timeline::before {
      left: 50%;
    }
  }
  .dark .timeline::before {
    background: linear-gradient(to bottom, transparent, #1e293b, transparent);
  }

  /* =============================================
     PROGRESS BAR
  ============================================= */
  #scroll-progress {
    position: fixed;
    top: 0; left: 0;
    height: 3px;
    width: 0%;
    background: linear-gradient(90deg, #003366, #c5a059);
    z-index: 100;
    transition: width 100ms linear;
  }

  /* =============================================
     HERO GRID PATTERN
  ============================================= */
  .hero-grid {
    background-image:
      linear-gradient(rgba(0,51,102,0.04) 1px, transparent 1px),
      linear-gradient(90deg, rgba(0,51,102,0.04) 1px, transparent 1px);
    background-size: 40px 40px;
  }
  .dark .hero-grid {
    background-image:
      linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
  }

  /* =============================================
     FADE-IN ANIMATION FOR HERO
  ============================================= */
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .animate-fadeup {
    opacity: 0;
    animation: fadeUp 0.7s ease forwards;
  }
  .delay-1 { animation-delay: 0.1s; }
  .delay-2 { animation-delay: 0.25s; }
  .delay-3 { animation-delay: 0.4s; }
  .delay-4 { animation-delay: 0.55s; }

  /* =============================================
     WHATSAPP FLOAT
  ============================================= */
  .wa-float {
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 50;
    width: 3.25rem;
    height: 3.25rem;
    background: #25d366;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 20px rgba(37,211,102,0.45);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .wa-float:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 28px rgba(37,211,102,0.55);
  }
  @keyframes wa-pulse {
    0%, 100% { box-shadow: 0 4px 20px rgba(37,211,102,0.45), 0 0 0 0 rgba(37,211,102,0.3); }
    50%       { box-shadow: 0 4px 20px rgba(37,211,102,0.45), 0 0 0 10px rgba(37,211,102,0); }
  }
  .wa-float { animation: wa-pulse 2.5s ease infinite; }
</style>
</head>

<body class="font-sans bg-bg-light dark:bg-bg-dark text-slate-900 dark:text-slate-100 transition-colors duration-300">

<!-- ================================================
     SCROLL PROGRESS BAR
================================================ -->
<div id="scroll-progress" aria-hidden="true"></div>

<!-- ================================================
     NAVIGATION
================================================ -->
<nav class="fixed top-0 inset-x-0 z-50 bg-bg-light/80 dark:bg-bg-dark/80 backdrop-blur-md border-b border-border-light dark:border-border-dark transition-colors duration-300" role="navigation" aria-label="Main navigation">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">

      <!-- Logo -->
      <a href="#" class="flex items-center gap-3" aria-label="SIMANTAB — Beranda">
        <div class="px-2 h-8 bg-primary dark:bg-white rounded-lg flex items-center justify-center" aria-hidden="true">
          <span class="text-white dark:text-black font-bold text-[10px] tracking-widest uppercase">E-AUDIT</span>
        </div>
        <div class="flex flex-col leading-none">
          <span class="font-display font-bold text-sm sm:text-base tracking-tight text-slate-900 dark:text-white">Inspektorat</span>
          <span class="text-[8px] sm:text-[9px] uppercase tracking-[0.15em] text-slate-500 font-semibold">Kab. Rembang</span>
        </div>
      </a>

      <!-- Desktop Nav -->
      <div class="hidden md:flex items-center gap-8 text-sm font-medium">
        <a href="#" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-white transition-colors">Beranda</a>
        <a href="#audit-process" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-white transition-colors">Alur Kerja</a>
        <a href="#regulasi" class="text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-white transition-colors">Regulasi</a>

        <div class="flex items-center gap-4 pl-4 border-l border-border-light dark:border-border-dark">
          <!-- Dark mode toggle -->
          <button
            onclick="toggleDark()"
            class="p-2 rounded-full border border-border-light dark:border-border-dark hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition-colors"
            aria-label="Toggle dark mode"
          >
            <span class="material-icons-round text-sm block dark:hidden" aria-hidden="true">dark_mode</span>
            <span class="material-icons-round text-sm hidden dark:block" aria-hidden="true">light_mode</span>
          </button>

          <a href="/admin" class="bg-blue-600 dark:bg-blue-500 text-white px-5 py-2 rounded-lg font-semibold hover:bg-blue-700 dark:hover:bg-blue-400 transition-all text-xs tracking-wider uppercase">
            Akses Portal
          </a>
        </div>
      </div>

      <!-- Mobile Controls -->
      <div class="md:hidden flex items-center gap-1">
        <button onclick="toggleDark()" class="p-2 text-slate-600 dark:text-slate-400" aria-label="Toggle dark mode">
          <span class="material-icons-round block dark:hidden" aria-hidden="true">dark_mode</span>
          <span class="material-icons-round hidden dark:block" aria-hidden="true">light_mode</span>
        </button>
        <button onclick="toggleMenu()" class="p-2 text-slate-600 dark:text-slate-400" aria-label="Toggle menu" aria-expanded="false" aria-controls="mobile-menu" id="menu-btn">
          <span class="material-icons-round" id="menu-icon" aria-hidden="true">menu</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile Menu -->
  <div id="mobile-menu" class="hidden md:hidden bg-bg-light dark:bg-bg-dark border-t border-border-light dark:border-border-dark px-4 py-6 space-y-4">
    <a href="#" class="block text-slate-700 dark:text-slate-300 font-semibold">Beranda</a>
    <a href="#audit-process" class="block text-slate-700 dark:text-slate-300 font-semibold">Alur Kerja</a>
    <a href="#regulasi" class="block text-slate-700 dark:text-slate-300 font-semibold">Regulasi</a>
    <a href="/admin" class="block text-center bg-primary dark:bg-white dark:text-black text-white py-3 rounded-lg font-bold">Akses Portal</a>
  </div>
</nav>

<!-- ================================================
     HERO SECTION
================================================ -->
<section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden hero-grid">
  <!-- Radial gradient overlay -->
  <div class="absolute inset-0 bg-gradient-to-b from-bg-light/60 via-transparent to-bg-light dark:from-bg-dark/60 dark:to-bg-dark pointer-events-none" aria-hidden="true"></div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

    <!-- Status badge -->
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 border border-border-light dark:border-border-dark text-[10px] font-bold uppercase tracking-[0.2em] mb-8 text-slate-500 dark:text-slate-400 animate-fadeup delay-1">
      <span class="relative flex h-2 w-2" aria-hidden="true">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
      </span>
      Sistem Audit Internal Terintegrasi
    </div>

    <h1 class="font-display text-5xl lg:text-7xl font-extrabold tracking-[-0.03em] mb-6 text-slate-900 dark:text-white max-w-5xl mx-auto leading-[1.1] animate-fadeup delay-2">
      SIMANTAB<br class="hidden lg:block"/>
      <span class="text-primary dark:text-blue-400">Inspektorat Daerah</span><br class="hidden lg:block"/>
      Kabupaten Rembang
    </h1>

    <p class="text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed animate-fadeup delay-3">
      Mewujudkan tata kelola pemerintahan yang bersih dan transparan melalui otomatisasi pemantauan, analisis risiko berbasis data, dan pelaporan terintegrasi.
    </p>

    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 animate-fadeup delay-4">
      <a href="https://simantab.rembangkab.go.id/"
         class="w-full sm:w-auto px-6 py-3 bg-blue-600 dark:bg-blue-500 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-500/25 hover:bg-blue-700 dark:hover:bg-blue-400 hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
        Akses Portal Audit
        <span class="material-icons-round text-xs" aria-hidden="true">login</span>
      </a>
      <a href="#panduan"
         class="w-full sm:w-auto px-6 py-3 border border-blue-600 dark:border-blue-400 text-blue-600 dark:text-blue-400 text-sm font-semibold rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-200 flex items-center justify-center gap-2">
        Unduh Panduan
        <span class="material-icons-round text-xs" aria-hidden="true">download</span>
      </a>
    </div>
  </div>
</section>

<!-- ================================================
     PARTNER LOGOS MARQUEE
================================================ -->
<section class="py-8 md:py-12 border-y border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/20 relative" aria-label="Mitra dan integrasi">

  <div class="overflow-hidden relative">
    <!-- Fade edges -->
    <div class="absolute inset-y-0 left-0 w-16 md:w-24 bg-gradient-to-r from-white dark:from-slate-900 to-transparent z-10 pointer-events-none"></div>
    <div class="absolute inset-y-0 right-0 w-16 md:w-24 bg-gradient-to-l from-white dark:from-slate-900 to-transparent z-10 pointer-events-none"></div>

    <?php
      $logos = [
        ['file' => 'bpk.svg',       'name' => 'BPK RI'],
        ['file' => 'bpkp.png',      'name' => 'BPKP'],
        ['file' => 'sipd.png',      'name' => 'SIPD RI'],
        ['file' => 'kpk.png',       'name' => 'KPK'],
        ['file' => 'kemendagri.png','name' => 'KEMENDAGRI'],
        ['file' => 'rembang.png',   'name' => 'PEMKAB REMBANG'],
      ];
    ?>

    <div class="marquee-track opacity-60 hover:opacity-100 transition-opacity duration-500 grayscale hover:grayscale-0">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = 0; $i < 2; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $logos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $logo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
          <div class="inline-flex items-center gap-2 mx-8 md:mx-12 flex-shrink-0">
            <img
              src="<?php echo e(asset('images/logos/' . $logo['file'])); ?>"
              alt="<?php echo e($logo['name']); ?>"
              class="h-7 md:h-9 w-auto object-contain"
              loading="lazy"
              decoding="async"
            />
            <span class="font-semibold text-xs md:text-sm tracking-tight text-slate-700 dark:text-slate-300 whitespace-nowrap">
              <?php echo e($logo['name']); ?>

            </span>
          </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

  </div>
</section>

<!-- ================================================
     AUDIT PROCESS TIMELINE
================================================ -->
<section id="audit-process" class="py-24 border-t border-border-light dark:border-border-dark">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

    <header class="text-center mb-16">
      <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Workflow</span>
      <h2 class="font-display text-4xl font-bold tracking-tight mt-2 mb-4">Proses E-Audit Digital</h2>
      <p class="text-slate-600 dark:text-slate-400 max-w-lg mx-auto">Tahapan audit modern yang transparan, akuntabel, dan terintegrasi</p>
    </header>

    <!-- Timeline items -->
    <ol class="space-y-10 relative timeline">

      <!-- Step 1 -->
      <li class="relative flex items-start justify-between md:justify-normal md:odd:flex-row-reverse group">
        <div class="flex items-center justify-center w-10 h-10 rounded-full border border-border-light dark:border-border-dark bg-bg-light dark:bg-bg-dark text-slate-400 group-hover:text-primary dark:group-hover:text-white transition-colors shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 mt-1">
          <span class="material-icons-round text-sm" aria-hidden="true">assignment</span>
        </div>
        <div class="w-[calc(100%-3.5rem)] md:w-[calc(50%-2.5rem)] p-6 rounded-2xl border border-border-light dark:border-border-dark hover:border-slate-300 dark:hover:border-slate-700 transition-colors bg-card-light dark:bg-card-dark">
          <p class="text-xs font-semibold text-primary dark:text-blue-400 mb-1 uppercase tracking-wide">Tahap Inisiasi</p>
          <h3 class="font-display font-bold text-lg mb-1">Perencanaan &amp; Risk Assessment</h3>
          <p class="flex items-center gap-1.5 text-xs text-slate-400 mb-3">
            <span class="material-icons-round text-xs" aria-hidden="true">settings_input_component</span> Integrasi Data SIPD
          </p>
          <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 leading-relaxed">Identifikasi area berisiko tinggi melalui analisis data otomatis untuk menentukan fokus pemeriksaan yang lebih akurat.</p>
          <div class="flex flex-wrap gap-2">
            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded text-[10px] font-semibold">PKPT</span>
            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded text-[10px] font-semibold">Risk Analysis</span>
            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded text-[10px] font-semibold">Mapping</span>
          </div>
        </div>
      </li>

      <!-- Step 2 -->
      <li class="relative flex items-start justify-between md:justify-normal md:odd:flex-row-reverse group">
        <div class="flex items-center justify-center w-10 h-10 rounded-full border border-border-light dark:border-border-dark bg-bg-light dark:bg-bg-dark text-slate-400 group-hover:text-primary dark:group-hover:text-white transition-colors shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 mt-1">
          <span class="material-icons-round text-sm" aria-hidden="true">analytics</span>
        </div>
        <div class="w-[calc(100%-3.5rem)] md:w-[calc(50%-2.5rem)] p-6 rounded-2xl border border-border-light dark:border-border-dark hover:border-slate-300 dark:hover:border-slate-700 transition-colors bg-card-light dark:bg-card-dark">
          <p class="text-xs font-semibold text-primary dark:text-blue-400 mb-1 uppercase tracking-wide">Tahap Eksekusi</p>
          <h3 class="font-display font-bold text-lg mb-1">Pelaksanaan Audit Lapangan</h3>
          <p class="flex items-center gap-1.5 text-xs text-slate-400 mb-3">
            <span class="material-icons-round text-xs" aria-hidden="true">verified_user</span> Real-time Verification
          </p>
          <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 leading-relaxed">Pengumpulan bukti audit secara elektronik, pengujian substantif, dan verifikasi dokumen langsung melalui portal E-Audit.</p>
          <div class="flex flex-wrap gap-2">
            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded text-[10px] font-semibold">Digital Evidence</span>
            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded text-[10px] font-semibold">E-Kertas Kerja</span>
            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded text-[10px] font-semibold">Sampling AI</span>
          </div>
        </div>
      </li>

      <!-- Step 3 -->
      <li class="relative flex items-start justify-between md:justify-normal md:odd:flex-row-reverse group">
        <div class="flex items-center justify-center w-10 h-10 rounded-full border border-border-light dark:border-border-dark bg-bg-light dark:bg-bg-dark text-slate-400 group-hover:text-primary dark:group-hover:text-white transition-colors shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 mt-1">
          <span class="material-icons-round text-sm" aria-hidden="true">description</span>
        </div>
        <div class="w-[calc(100%-3.5rem)] md:w-[calc(50%-2.5rem)] p-6 rounded-2xl border border-border-light dark:border-border-dark hover:border-slate-300 dark:hover:border-slate-700 transition-colors bg-card-light dark:bg-card-dark">
          <p class="text-xs font-semibold text-primary dark:text-blue-400 mb-1 uppercase tracking-wide">Tahap Finalisasi</p>
          <h3 class="font-display font-bold text-lg mb-1">Pelaporan &amp; Tindak Lanjut</h3>
          <p class="flex items-center gap-1.5 text-xs text-slate-400 mb-3">
            <span class="material-icons-round text-xs" aria-hidden="true">check_circle</span> LHP Digital Ready
          </p>
          <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 leading-relaxed">Penyusunan Laporan Hasil Pemeriksaan (LHP) otomatis dan pemantauan tindak lanjut rekomendasi secara daring.</p>
          <div class="flex flex-wrap gap-2">
            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded text-[10px] font-semibold">LHP</span>
            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded text-[10px] font-semibold">TLHP Online</span>
            <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded text-[10px] font-semibold">Dashboard</span>
          </div>
        </div>
      </li>

    </ol>
  </div>
</section>

<!-- ================================================
     FOOTER
================================================ -->
<footer class="py-16 bg-white dark:bg-bg-dark border-t border-slate-100 dark:border-border-dark transition-colors duration-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid md:grid-cols-4 gap-10 mb-14">

      <!-- Brand -->
      <div class="col-span-2">
        <div class="flex items-center gap-3 mb-5">
          <div class="px-2 h-8 bg-primary dark:bg-white rounded-lg flex items-center justify-center" aria-hidden="true">
            <span class="text-white dark:text-black font-bold text-[10px] tracking-widest">E-AUDIT</span>
          </div>
          <div class="flex flex-col leading-none">
            <span class="font-display font-extrabold text-lg tracking-tight text-slate-900 dark:text-white uppercase">Inspektorat</span>
            <span class="text-[9px] uppercase tracking-[0.15em] text-slate-500 font-semibold">Kabupaten Rembang</span>
          </div>
        </div>
        <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm leading-relaxed">
          Standar profesional untuk audit digital dan manajemen integritas data di lingkungan Pemerintah Kabupaten Rembang.
        </p>
      </div>

      <!-- Layanan -->
      <nav aria-label="Layanan">
        <h4 class="font-semibold text-slate-900 dark:text-white mb-5 text-sm uppercase tracking-wider">Layanan</h4>
        <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400">
          <li><a href="#" class="hover:text-primary dark:hover:text-white transition-colors">E-Planning</a></li>
          <li><a href="#" class="hover:text-primary dark:hover:text-white transition-colors">E-Execution</a></li>
          <li><a href="#" class="hover:text-primary dark:hover:text-white transition-colors">E-Reporting</a></li>
        </ul>
      </nav>

      <!-- Tautan Cepat -->
      <nav aria-label="Tautan cepat">
        <h4 class="font-semibold text-slate-900 dark:text-white mb-5 text-sm uppercase tracking-wider">Tautan Cepat</h4>
        <ul class="space-y-3 text-sm text-slate-500 dark:text-slate-400">
          <li><a href="#" class="hover:text-primary dark:hover:text-white transition-colors">Profil Instansi</a></li>
          <li><a href="#" class="hover:text-primary dark:hover:text-white transition-colors">Regulasi &amp; Hukum</a></li>
          <li><a href="#" class="hover:text-primary dark:hover:text-white transition-colors">Pusat Bantuan</a></li>
        </ul>
      </nav>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center pt-8 border-t border-slate-100 dark:border-border-dark gap-4 text-xs text-slate-400 dark:text-slate-500">
      <p>© 2026 Inspektorat Kabupaten Rembang. All rights reserved.</p>
      <nav class="flex gap-6" aria-label="Legal links">
        <a href="#" class="hover:text-primary dark:hover:text-white transition-colors">Kebijakan Privasi</a>
        <a href="#" class="hover:text-primary dark:hover:text-white transition-colors">Syarat &amp; Ketentuan</a>
        <a href="#" class="hover:text-primary dark:hover:text-white transition-colors">Kontak Kami</a>
      </nav>
    </div>
  </div>
</footer>

<!-- ================================================
     WHATSAPP FLOAT BUTTON
================================================ -->
<a href="https://wa.me/628123456789?text=Halo%20Admin%20Inspektorat%20Rembang%2C%20saya%20ingin%20bertanya%20terkait%20SIMANTAB"
   class="wa-float"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="Hubungi kami di WhatsApp">
  <svg width="28" height="28" fill="white" viewBox="0 0 24 24" aria-hidden="true">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
  </svg>
</a>

<!-- ================================================
     SCRIPTS (deferred, non-blocking)
================================================ -->
<script>
  // ── Dark mode (run before paint to avoid flash) ──
  (function () {
    const saved = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (saved === 'dark' || (!saved && prefersDark)) {
      document.documentElement.classList.add('dark');
    }
  })();

  function toggleDark() {
    const html = document.documentElement;
    html.classList.toggle('dark');
    localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
  }

  // ── Mobile menu ──
  function toggleMenu() {
    const menu = document.getElementById('mobile-menu');
    const icon = document.getElementById('menu-icon');
    const btn  = document.getElementById('menu-btn');
    const open = menu.classList.toggle('hidden');
    icon.innerText = open ? 'menu' : 'close';
    btn.setAttribute('aria-expanded', String(!open));
  }

  // ── Scroll progress bar ──
  window.addEventListener('scroll', function () {
    const scrolled = document.documentElement.scrollTop;
    const total    = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    document.getElementById('scroll-progress').style.width = (scrolled / total * 100) + '%';
  }, { passive: true });
</script>

</body>
</html><?php /**PATH /Users/user/Documents/SIMANTAP/simantap/resources/views/welcome.blade.php ENDPATH**/ ?>