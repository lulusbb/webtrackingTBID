<div id="sidebar" class="sidebar">
  <div class="sidebar-wrapper active">
    {{-- ========= Header ========= --}}
    <div class="sidebar-header position-relative">
      <div class="d-flex justify-content-between align-items-center">
        <div class="logo">
          <img src="{{ asset('mazer/assets/images/logo/logo.png') }}" alt="Logo" height="40">
        </div>

        {{-- Theme toggle --}}
        <div class="theme-toggle d-flex gap-2 align-items-center mt-2">
          <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" width="20" height="20" viewBox="0 0 21 21">
            <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10.5 14.5c2.219 0 4-1.763 4-3.982a4.003 4.003 0 0 0-4-4.018c-2.219 0-4 1.781-4 4c0 2.219 1.781 4 4 4zM4.136 4.136L5.55 5.55m9.9 9.9l1.414 1.414M1.5 10.5h2m14 0h2M4.135 16.863L5.55 15.45m9.899-9.9l1.414-1.415M10.5 19.5v-2m0-14v-2" opacity=".3"/>
            </g>
          </svg>
          <div class="form-check form-switch fs-6">
            <input class="form-check-input" type="checkbox" id="theme-toggle">
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" width="20" height="20" viewBox="0 0 24 24">
            <path fill="currentColor" d="m17.75 4.09-2.53 1.94.91 3.06-2.63-1.81-2.63 1.81.91-3.06-2.53-1.94L12.44 4l1.06-3 1.06 3 3.19.09m3.5 6.91-1.64 1.25.59 1.98-1.7-1.17-1.7 1.17.59-1.98L15.75 11l2.06-.05L18.5 9l.69 1.95 2.06.05m-2.28 4.95c.83-.08 1.72 1.1 1.19 1.85-.32.45-.66.87-1.08 1.27C15.17 23 8.84 23 4.94 19.07c-3.91-3.9-3.91-10.24 0-14.14.4-.4.82-.76 1.27-1.08.75-.53 1.93.36 1.85 1.19-.27 2.86.69 5.83 2.89 8.02a9.96 9.96 0 0 0 8.02 2.89m-1.64 2.02a12.08 12.08 0 0 1-7.8-3.47c-2.17-2.19-3.33-5-3.49-7.82-2.81 3.14-2.7 7.96.31 10.98 3.02 3.01 7.84 3.12 10.98.31Z"/>
          </svg>
        </div>

        <div class="sidebar-toggler x">
          <a href="#" class="sidebar-hide d-xl-none d-block" data-no-loading aria-label="Close sidebar">
            <i class="bi bi-x bi-middle"></i>
          </a>
        </div>
      </div>
    </div>

@php
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Route as R;

  $role = Auth::check() ? strtolower(Auth::user()->role ?? '') : null;
  $is   = fn (...$names) => request()->routeIs(...$names);

  $isAdminDashActive = request()->routeIs('admin.dashboard') || request()->is('admin');
  $isCeoDashActive   = request()->routeIs('ceo.dashboard')   || $isAdminDashActive;

  $studioStagesActive = $is('studio.denah*','studio.exteriors.*','studio.mep*','studio.delegasirab.*','studio.akhir*');
  $studioSurveyActive = $is('studio.kliensurvei*','studio.survei_cancel.*');
  $sipilActive   = $is('project.struktur3d*','project.skema*','project.rab*');
  $arsitekActive = $is('project.mou*');
  $proyekActive  = $is('project.proyek.*');
  $selesaiActive = $is('project.selesai.*');
  $settingsActive = $is('settings.*');

  $adminDashboardUrl = R::has('admin.dashboard') ? route('admin.dashboard') : url('/admin');
  $ceoDashboardUrl   = R::has('ceo.dashboard')   ? route('ceo.dashboard')   : $adminDashboardUrl;
@endphp

    {{-- ========= Menu ========= --}}
    <div class="sidebar-menu mt-3 hide-scrollbar">
      <ul class="menu">

        {{-- CEO --}}
        @if($role === 'ceo')
          <li class="sidebar-title">CEO</li>
          <li class="sidebar-item {{ $isCeoDashActive ? 'active' : '' }}">
            <a href="{{ $adminDashboardUrl }}" class="sidebar-link">
              <i class="bi bi-grid-fill"></i><span>Dashboard Utama</span>
            </a>
          </li>
          <li class="sidebar-item {{ $selesaiActive ? 'active' : '' }}">
            <a href="{{ route('project.selesai.index') }}" class="sidebar-link" @if($selesaiActive) aria-current="page" @endif>
              <i class="bi bi-check2-square"></i><span>Proyek Selesai</span>
            </a>
          </li>
        @endif

        {{-- Admin --}}
        @if($role === 'admin')
          <li class="sidebar-title">Menu Admin</li>
          <li class="sidebar-item {{ $isAdminDashActive ? 'active' : '' }}">
            <a href="{{ $adminDashboardUrl }}" class="sidebar-link" aria-current="{{ $isAdminDashActive ? 'page' : 'false' }}" data-no-loading>
              <i class="bi bi-grid-fill"></i><span>Dashboard Utama</span>
            </a>
          </li>
          <li class="sidebar-item {{ $is('admin.akun.*') ? 'active' : '' }}">
            <a href="{{ route('admin.akun.index') }}" class="sidebar-link">
              <i class="bi bi-people"></i><span>Akun Karyawan</span>
            </a>
          </li>
          <li class="sidebar-item {{ $settingsActive ? 'active' : '' }}">
            <a href="{{ route('settings.index') }}" class="sidebar-link"
              aria-current="{{ $settingsActive ? 'page' : 'false' }}">
              <i class="bi bi-gear-fill"></i>
              <span>Pengaturan</span>
            </a>
          </li>

        @endif

        {{-- Marketing --}}
        @if(in_array($role, ['admin','marketing']))
          <li class="sidebar-title">Marketing</li>
          <li class="sidebar-item {{ $is('marketing.dashboard') ? 'active' : '' }}">
            <a href="{{ route('marketing.dashboard') }}" class="sidebar-link" aria-current="{{ $is('marketing.dashboard') ? 'page' : 'false' }}">
              <i class="bi bi-bar-chart-line-fill"></i><span>Dashboard</span>
            </a>
          </li>
          <li class="sidebar-item {{ $is('marketing.klien.*') ? 'active' : '' }}">
            <a href="{{ route('marketing.klien.index') }}" class="sidebar-link">
              <i class="bi bi-people-fill"></i><span>Data Klien</span>
            </a>
          </li>
          <li class="sidebar-item {{ $is('marketing.laporan') ? 'active' : '' }}">
            <a href="{{ route('marketing.laporan') }}" class="sidebar-link">
              <i class="bi bi-file-earmark-text-fill"></i><span>Laporan</span>
            </a>
          </li>
        @endif

        {{-- Studio --}}
        @if(in_array($role, ['admin','studio']))
          <li class="sidebar-title">Studio</li>
          <li class="sidebar-item {{ $is('studio.dashboard') ? 'active' : '' }}">
            <a href="{{ route('studio.dashboard') }}" class="sidebar-link">
              <i class="bi bi-easel-fill"></i><span>Dashboard</span>
            </a>
          </li>

          <li class="sidebar-item has-sub {{ $studioSurveyActive ? 'active' : '' }}">
            <a href="#" class="sidebar-link has-caret" data-no-loading aria-expanded="{{ $studioSurveyActive ? 'true' : 'false' }}">
              <i class="bi bi-people-fill"></i><span>Klien Survei</span>
              <i class="bi bi-chevron-right caret ms-auto"></i>
            </a>
            <ul class="submenu">
              <li class="submenu-item {{ $is('studio.kliensurvei') ? 'active' : '' }}">
                <a href="{{ route('studio.kliensurvei') }}" class="submenu-link" data-no-loading>Klien Survei</a>
              </li>
              <li class="submenu-item {{ $is('studio.survei_cancel.index') ? 'active' : '' }}">
                <a href="{{ route('studio.survei_cancel.index') }}" class="submenu-link" data-no-loading>Klien Cancel</a>
              </li>
            </ul>
          </li>

          <li class="sidebar-item has-sub {{ $studioStagesActive ? 'active' : '' }}">
            <a href="#" class="sidebar-link has-caret" data-no-loading aria-expanded="{{ $studioStagesActive ? 'true' : 'false' }}">
              <i class="bi bi-collection-fill"></i><span>Studio</span>
              <i class="bi bi-chevron-right caret ms-auto"></i>
            </a>
            <ul class="submenu">
              <li class="submenu-item {{ $is('studio.denah*') ? 'active' : '' }}">
                <a href="{{ route('studio.denah') }}" class="submenu-link">Denah & Moodboard</a>
              </li>
              <li class="submenu-item {{ $is('studio.exteriors.*') ? 'active' : '' }}">
                <a href="{{ route('studio.exteriors.index') }}" class="submenu-link">3D Desain</a>
              </li>
              <li class="submenu-item {{ $is('studio.mep*') ? 'active' : '' }}">
                <a href="{{ route('studio.mep') }}" class="submenu-link">MEP & Spek Material</a>
              </li>
              <li class="submenu-item {{ $is('studio.delegasirab.*') ? 'active' : '' }}">
                <a href="{{ route('studio.delegasirab.index') }}" class="submenu-link">Delegasi RAB</a>
              </li>
              <li class="submenu-item {{ $is('studio.akhir*') ? 'active' : '' }}">
                <a href="{{ route('studio.akhir') }}" class="submenu-link">Serah Terima Desain</a>
              </li>
            </ul>
          </li>
        @endif

        {{-- Project --}}
        @if(in_array($role, ['admin','project']))
          <li class="sidebar-title">Project</li>
          <li class="sidebar-item {{ $is('project.dashboard') ? 'active' : '' }}">
            <a href="{{ route('project.dashboard') }}" class="sidebar-link">
              <i class="bi bi-hammer"></i><span>Dashboard</span>
            </a>
          </li>

          <li class="sidebar-item has-sub {{ $sipilActive ? 'active' : '' }}">
            <a href="#" class="sidebar-link has-caret" data-no-loading aria-expanded="{{ $sipilActive ? 'true' : 'false' }}">
              <i class="bi bi-clipboard-data-fill"></i><span>Sipil Project</span>
              <i class="bi bi-chevron-right caret ms-auto"></i>
            </a>
            <ul class="submenu">
              <li class="submenu-item {{ $is('project.struktur3d*') ? 'active' : '' }}">
                <a class="submenu-link" href="{{ route('project.struktur3d.index') }}">3D Struktur</a>
              </li>
              <li class="submenu-item {{ $is('project.skema*') ? 'active' : '' }}">
                <a class="submenu-link" href="{{ route('project.skema.index') }}">Skema Plumbing</a>
              </li>
              <li class="submenu-item {{ $is('project.rab*') ? 'active' : '' }}">
                <a class="submenu-link" href="{{ route('project.rab.index') }}">RAB</a>
              </li>
            </ul>
          </li>

          <li class="sidebar-item has-sub {{ $arsitekActive ? 'active' : '' }}">
            <a href="#" class="sidebar-link has-caret" data-no-loading aria-expanded="{{ $arsitekActive ? 'true' : 'false' }}">
              <i class="bi bi-diagram-3-fill"></i><span>Arsitek Project</span>
              <i class="bi bi-chevron-right caret ms-auto"></i>
            </a>
            <ul class="submenu">
              <li class="submenu-item {{ $is('project.mou*') ? 'active' : '' }}">
                <a class="submenu-link" href="{{ route('project.mou.index') }}">MOU</a>
              </li>
            </ul>
          </li>

          <li class="sidebar-item {{ $proyekActive ? 'active' : '' }}">
            <a href="{{ route('project.proyek.index') }}" class="sidebar-link" @if($proyekActive) aria-current="page" @endif>
              <i class="bi bi-hourglass-split"></i><span>Proyek Berjalan</span>
            </a>
          </li>

          <li class="sidebar-item {{ $selesaiActive ? 'active' : '' }}">
            <a href="{{ route('project.selesai.index') }}" class="sidebar-link" @if($selesaiActive) aria-current="page" @endif>
              <i class="bi bi-check2-square"></i><span>Proyek Selesai</span>
            </a>
          </li>
        @endif

        @auth
          <li class="sidebar-title">Akun</li>
          <li class="sidebar-item">
            <form id="sidebar-logout" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
            <a href="#" class="sidebar-link" data-no-loading onclick="event.preventDefault(); document.getElementById('sidebar-logout').submit();">
              <i class="bi bi-box-arrow-right"></i><span>Logout</span>
            </a>
          </li>
        @endauth

      </ul>
    </div>
  </div>
</div>

{{-- ===== CSS override WAJIB di bawah ini (bukan @push) ===== --}}
<style>
  /* ===== Layout dasar sidebar agar bisa scroll ===== */
  #sidebar {
    overflow: hidden;
  }
  #sidebar .sidebar-wrapper{
    height: 100vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }
  #sidebar .sidebar-header{
    flex: 0 0 auto;
    position: sticky;
    top: 0;
    z-index: 5;
    background: inherit;
  }
  #sidebar .sidebar-menu{
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
    padding-bottom: 1rem;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
  }

  /* ========= Submenu agar tidak “terpotong” saat dibuka ========= */
  #sidebar .menu > li.has-sub > .submenu{
    position: relative;
    z-index: 40;
    overflow: visible;
    max-height: none !important;
    padding-bottom: .8rem !important;
    margin-bottom: .8rem !important;
  }
  #sidebar .menu .submenu .submenu-link{ position: relative; z-index: 41; }

  /* Hilangkan dekorasi kapsul bawaan Mazer (opsional) */
  #sidebar .menu > li.has-sub::after,
  #sidebar .menu > li.has-sub.active::after,
  #sidebar .menu > li > a.sidebar-link::before,
  #sidebar .menu > li > a.sidebar-link::after,
  #sidebar .menu .sidebar-title::before,
  #sidebar .menu .sidebar-title::after,
  #sidebar .menu .divider,
  #sidebar .menu .menu-divider,
  #sidebar .menu hr {
    content: none !important;
    display: none !important;
  }
  #sidebar .menu > li{ position: relative; z-index: 1; }
  #sidebar .menu .sidebar-title{ position: relative; z-index: 0; margin-top: 1rem; }

  /* Hide scrollbar, keep scroll working */
  .hide-scrollbar {
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
  }
  .hide-scrollbar::-webkit-scrollbar {
    width: 0;
    height: 0;
  }

  /* ===== Caret (panah) di parent submenu ===== */
  #sidebar .sidebar-link.has-caret{
    display: flex;
    align-items: center;
    gap: .75rem;
  }
  #sidebar .sidebar-link .caret{
    margin-left: auto;
    font-size: .9rem;
    opacity: .7;
    transition: transform .2s ease, opacity .2s ease;
  }
  /* Rotasi saat aktif / terbuka */
  #sidebar .sidebar-item.has-sub.active > a .caret,
  #sidebar .sidebar-item.has-sub > a[aria-expanded="true"] .caret{
    transform: rotate(90deg);
    opacity: 1;
  }
  /* ==== Sidebar left alignment fix (Mazer) ==== */
:root{
  --sb-pad-left: 14px;   /* kiri semua item */
  --sb-icon-w:   20px;   /* lebar area icon bi-... */
}

/* Semua item menu sama padding kirinya */
.sidebar .sidebar-item > .sidebar-link{
  padding-left: var(--sb-pad-left) !important;
  display:flex; align-items:center; gap:.5rem;
}

/* Parent yang punya submenu jangan nambah indent */
.sidebar .sidebar-item.has-sub > .sidebar-link{
  padding-left: var(--sb-pad-left) !important;
}

/* Samakan lebar area ikon supaya teks start sejajar */
.sidebar .sidebar-item > .sidebar-link i{
  width: var(--sb-icon-w); 
  text-align:center; 
  flex: 0 0 var(--sb-icon-w);
  margin-right:.25rem;         /* jarak kecil setelah icon */
}

/* Submenu (anak-anak) tetap menjorok sedikit */
.sidebar .submenu .submenu-item > a{
  padding-left: calc(var(--sb-pad-left) + var(--sb-icon-w) + 12px) !important;
}

/* Optional: rapikan posisi chevron di kanan */
.sidebar .sidebar-item.has-sub > .sidebar-link::after{
  right: 12px;                 /* posisi chevron */
}
</style>
