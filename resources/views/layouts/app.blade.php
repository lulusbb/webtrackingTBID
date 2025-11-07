{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'App') }}</title>

    <!-- ==== (NEW) Tambah flag agar konten tidak tampil sebelum font siap ==== -->
    <script>document.documentElement.classList.add('fonts-loading');</script>

    <!-- ==== (NEW) Poppins: preconnect + preload + load ==== -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Preload stylesheet (non-blocking), lalu aktifkan -->
    <link rel="preload"
          as="style"
          href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
          media="print"
          onload="this.media='all'">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('mazer/assets/extensions/@fortawesome/fontawesome-free/css/all.min.css') }}">

    <!-- Mazer CSS -->
    <link rel="stylesheet" href="{{ asset('mazer/assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/css/app-dark.css') }}">

    <!-- DataTables CSS (konsisten 1.13.6) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('mazer/assets/css/custom.css') }}">

    <style>
      /* ==== Font global: Poppins (sinkron dengan Bootstrap vars) ==== */
      :root{
        --tbid-font: 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue',
                     Arial, 'Noto Sans', 'Apple Color Emoji','Segoe UI Emoji';
        --bs-body-font-family: var(--tbid-font);
      }
      html, body{
        font-family: var(--tbid-font);
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
      }
      /* Sembunyikan konten sekejap sampai font siap → cegah “lompat” font */
      html.fonts-loading body{ visibility: hidden; }

      html[data-bs-theme="dark"] { background-color: #0f1115; }
      #loading-overlay {
        position: fixed; inset: 0; display: none; align-items:center; justify-content:center;
        background: rgba(0,0,0,.45); z-index: 2000;
      }
    </style>

    <style>
      /* ===== Sidebar TBID (Mazer override) ===== */
      :root{
        --sidebar-bg:#1e1e2d;
        --sidebar-hover:#252538;
        --sidebar-active:#2a2a42;
        --accent-blue:#5160e0;     /* warna parent aktif */
        --hover-light:#E9EDF7;     /* latar hover terang agar teks hitam kebaca */
      }

      /* Container */
      #sidebar, .sidebar, .sidebar-wrapper{
        background:var(--sidebar-bg)!important;
        color:#fff!important;
      }

      /* Section title (Menu Admin, Marketing, dll) */
      .sidebar-wrapper .menu .sidebar-title,
      .sidebar-wrapper .menu .menu-title{
        color:#fff!important;
        opacity:.95!important;
      }

      /* Parent & submenu link: default putih */
      .sidebar-wrapper .menu .sidebar-item .sidebar-link,
      .sidebar-wrapper .menu .sidebar-item>a,
      .sidebar-wrapper .menu .submenu .submenu-item>a,
      .sidebar-wrapper .menu .submenu .submenu-link{
        color:#fff!important;
        border-radius:.55rem;
      }

      /* .text-muted di dalam link kadang override dari Mazer */
      .sidebar-wrapper .menu .sidebar-item .sidebar-link .text-muted{
        color:#fff!important;
        opacity:1!important;
      }

      /* Parent aktif = biru rata, teks & ikon putih */
      .sidebar-wrapper .menu .sidebar-item.active>.sidebar-link{
        background:var(--accent-blue)!important;
        color:#fff!important;
        box-shadow:none!important;
      }

      /* SUBMENU panel: transparan (tanpa bubble) */
      .sidebar-wrapper .menu .submenu{
        background:transparent!important;
        border:0!important;
        box-shadow:none!important;
        padding:.25rem 0 .5rem 0!important;
      }

      /* SUBMENU link: indent rapi, default putih */
      .sidebar-wrapper .menu .submenu .submenu-item>a,
      .sidebar-wrapper .menu .submenu .submenu-link{
        background:transparent!important;
        color:#fff!important;
        padding:.45rem 1rem .45rem 2.5rem;
      }

      /* SUBMENU aktif */
      .sidebar-wrapper .menu .submenu .submenu-item.active>a,
      .sidebar-wrapper .menu .submenu .active>.submenu-link{
        background:var(--sidebar-active)!important;
        color:#fff!important;
      }

      /* ===== HOVER: teks & ikon jadi HITAM (kecuali item aktif) ===== */
      .sidebar-wrapper .menu .sidebar-item:not(.active) > .sidebar-link:hover,
      .sidebar-wrapper .menu .sidebar-item:not(.active) .sidebar-link:hover{
        background:var(--hover-light)!important;
        color:#000!important;
      }
      .sidebar-wrapper .menu .submenu .submenu-item>a:hover,
      .sidebar-wrapper .menu .submenu .submenu-link:hover{
        background:var(--hover-light)!important;
        color:#000!important;
      }

      /* Ikon default putih */
      .sidebar-wrapper .menu .sidebar-item i,
      .sidebar-wrapper .menu .sidebar-item svg{
        color:#fff!important;
        stroke:#fff!important;
      }

      /* Ikon saat hover (ikut hitam), tapi biarkan putih untuk item aktif */
      .sidebar-wrapper .menu .sidebar-item:not(.active) > .sidebar-link:hover i,
      .sidebar-wrapper .menu .sidebar-item:not(.active) > .sidebar-link:hover svg,
      .sidebar-wrapper .menu .submenu .submenu-item>a:hover i,
      .sidebar-wrapper .menu .submenu .submenu-link:hover i,
      .sidebar-wrapper .menu .submenu .submenu-item>a:hover svg,
      .sidebar-wrapper .menu .submenu .submenu-link:hover svg{
        color:#000!important;
        stroke:#000!important;
      }

      /* Caret pada item bersubmenu */
      .sidebar-item.has-sub > .sidebar-link::after{
        border-color:#fff!important;
        color:#fff!important;
      }
      .sidebar-item.has-sub:not(.active) > .sidebar-link:hover::after{
        border-color:#000!important;
        color:#000!important;
      }

      /* (Opsional) scrollbar sidebar */
      .sidebar-wrapper::-webkit-scrollbar{ width:8px; }
      .sidebar-wrapper::-webkit-scrollbar-thumb{ background:#2b2b3d; border-radius:8px; }
      .sidebar-wrapper::-webkit-scrollbar-track{ background:#131320; }
    </style>

    @stack('head')
</head>
<body>
    <div id="loading-overlay">
        <div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div>
    </div>

    <div id="app">
        @include('layouts.sidebar')
        <div id="sidebar-backdrop"></div>
        <div id="main" class="layout-navbar">
            @include('layouts.header')

            <main id="main-content" class="container-fluid px-3 py-3">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- jQuery & Bootstrap --}}
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- DataTables JS (konsisten 1.13.6) --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    {{-- Mazer & Custom (sekali saja) --}}
    <script src="{{ asset('mazer/assets/js/app.js') }}"></script>
    <script src="{{ asset('mazer/assets/js/custom.js') }}"></script>

    {{-- Feather Icons (opsional) --}}
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- ==== (NEW) Lepas guard saat font siap ==== -->
    <script>
      (function(){
        if (document.fonts && document.fonts.ready) {
          document.fonts.ready.then(function(){
            document.documentElement.classList.remove('fonts-loading');
          });
        } else {
          window.addEventListener('load', function(){
            document.documentElement.classList.remove('fonts-loading');
          });
        }
      })();
    </script>

    @stack('scripts')
</body>
</html>
