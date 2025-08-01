<div id="sidebar" class="sidebar">
    <div class="sidebar-wrapper active">
       <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                <img src="{{ asset('mazer/assets/images/logo/logo.png') }}" alt="Logo" height="40">
            </div>

            <!-- Theme Switch + Close Button -->
            <div class="theme-toggle d-flex gap-2 align-items-center mt-2">
                <!-- Mode Switch -->
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--system-uicons" width="20" height="20" preserveAspectRatio="xMidYMid meet" viewBox="0 0 21 21"><g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M10.5 14.5c2.219 0 4-1.763 4-3.982a4.003 4.003 0 0 0-4-4.018c-2.219 0-4 1.781-4 4c0 2.219 1.781 4 4 4zM4.136 4.136L5.55 5.55m9.9 9.9l1.414 1.414M1.5 10.5h2m14 0h2M4.135 16.863L5.55 15.45m9.899-9.9l1.414-1.415M10.5 19.5v-2m0-14v-2" opacity=".3"></path><g transform="translate(-210 -1)"><path d="M220.5 2.5v2m6.5.5l-1.5 1.5"></path><circle cx="220.5" cy="11.5" r="4"></circle><path d="m214 5l1.5 1.5m5 14v-2m6.5-.5l-1.5-1.5M214 18l1.5-1.5m-4-5h2m14 0h2"></path></g></g></svg>
                    <div class="form-check form-switch fs-6">
                        <input class="form-check-input" type="checkbox" id="theme-toggle" />
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--mdi" width="20" height="20" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="m17.75 4.09l-2.53 1.94l.91 3.06l-2.63-1.81l-2.63 1.81l.91-3.06l-2.53-1.94L12.44 4l1.06-3l1.06 3l3.19.09m3.5 6.91l-1.64 1.25l.59 1.98l-1.7-1.17l-1.7 1.17l.59-1.98L15.75 11l2.06-.05L18.5 9l.69 1.95l2.06.05m-2.28 4.95c.83-.08 1.72 1.1 1.19 1.85c-.32.45-.66.87-1.08 1.27C15.17 23 8.84 23 4.94 19.07c-3.91-3.9-3.91-10.24 0-14.14c.4-.4.82-.76 1.27-1.08c.75-.53 1.93.36 1.85 1.19c-.27 2.86.69 5.83 2.89 8.02a9.96 9.96 0 0 0 8.02 2.89m-1.64 2.02a12.08 12.08 0 0 1-7.8-3.47c-2.17-2.19-3.33-5-3.49-7.82c-2.81 3.14-2.7 7.96.31 10.98c3.02 3.01 7.84 3.12 10.98.31Z"></path></svg>
            </div>

                <!-- Sidebar Toggler (untuk mobile) -->
                <div class="sidebar-toggler x">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>  
    </div>
    @php
            $role = auth()->user()->role;
            $studioSubmenuActive = Request::routeIs('studio.denah', 'studio.3d', 'studio.mep', 'studio.akhir');
            $projectSubmenuActive = Request::routeIs('project.struktur3d', 'project.plumbing', 'project.rab');
            $projectSubmenuActive = Request::routeIs('project.mous');
        @endphp

        <div class="sidebar-menu mt-3">
            <ul class="menu">

                {{-- Admin --}}
                @if($role == 'admin')
                    <li class="sidebar-title">Menu Admin</li>
                    <li class="sidebar-item {{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('admin.dashboard') }}" class='sidebar-link'>
                            <i class="bi bi-grid-fill"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="sidebar-item {{ request()->routeIs('admin.akun.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.akun.index') }}" class="sidebar-link">
                            <i class="bi bi-people"></i>
                            <span>Akun Karyawan</span>
                        </a>
                    </li>
                @endif

                {{-- Marketing --}}
                @if(in_array($role, ['admin', 'marketing']))
                    <li class="sidebar-title">Marketing</li>
                    <li class="sidebar-item {{ Request::routeIs('marketing.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('marketing.dashboard') }}" class='sidebar-link'>
                            <i class="bi bi-bar-chart-line-fill"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="sidebar-item {{ Request::routeIs('marketing.klien') ? 'active' : '' }}">
                        <a href="{{ route('marketing.klien.index') }}" class='sidebar-link'>
                            <i class="bi bi-people-fill"></i>
                            <span>Data Klien</span>
                        </a>
                    </li>
                    <li class="sidebar-item {{ Request::routeIs('marketing.laporan') ? 'active' : '' }}">
                        <a href="{{ route('marketing.laporan') }}" class='sidebar-link'>
                            <i class="bi bi-file-earmark-text-fill"></i>
                            <span>Laporan</span>
                        </a>
                    </li>
                @endif

                {{-- Studio --}}
                @if(in_array($role, ['admin', 'studio']))
                    <li class="sidebar-title">Studio</li>

                    <li class="sidebar-item {{ Request::routeIs('studio.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('studio.dashboard') }}" class='sidebar-link'>
                            <i class="bi bi-easel-fill"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ Request::routeIs('studio.kliensurvei') ? 'active' : '' }}">
                        <a href="{{ route('studio.kliensurvei') }}" class='sidebar-link'>
                            <i class="bi bi-people-fill"></i>
                            <span>Klien Survei</span>
                        </a>
                    </li>

                <li class="sidebar-item has-sub {{ Request::is('studio/denah-moodboard*') || Request::is('studio/3d-interior*') || Request::is('studio/mep-spek*') || Request::is('studio/tahap-akhir*') ? 'active' : '' }}">
                    <a href="#" class='sidebar-link'>
                        <i class="bi bi-collection-fill"></i>
                        <span>Studio</span>
                    </a>
                    <ul class="submenu {{ Request::is('studio/denah-moodboard*') || Request::is('studio/3d-interior*') || Request::is('studio/mep-spek*') || Request::is('studio/tahap-akhir*') ? 'active' : '' }}">
                        <li class="submenu-item {{ Request::routeIs('studio.denah') ? 'active' : '' }}">
                            <a href="{{ route('studio.denah') }}" class="submenu-link">Denah & Moodboard</a>
                        </li>
                        <li class="submenu-item {{ Request::routeIs('studio.3d') ? 'active' : '' }}">
                            <a href="{{ route('studio.3d') }}" class="submenu-link">3D Exterior & Interior</a>
                        </li>
                        <li class="submenu-item {{ Request::routeIs('studio.mep') ? 'active' : '' }}">
                            <a href="{{ route('studio.mep') }}" class="submenu-link">MEP & Spek Material</a>
                        </li>
                        <li class="submenu-item {{ Request::routeIs('studio.akhir') ? 'active' : '' }}">
                            <a href="{{ route('studio.akhir') }}" class="submenu-link">Tahap Akhir</a>
                        </li>
                    </ul>
                </li>


                @endif

                {{-- Project --}}
                @if(in_array($role, ['admin', 'project']))
                    <li class="sidebar-title">Project</li>

                    <li class="sidebar-item {{ Request::routeIs('project.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('project.dashboard') }}" class='sidebar-link'>
                            <i class="bi bi-hammer"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                <li class="sidebar-item has-sub {{ Request::is('project/3d-struktur*') || Request::is('project/plumbing*') || Request::is('project/rab*') ? 'active' : '' }}">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-clipboard-data-fill"></i>
                        <span>Sipil Project</span>
                    </a>
                    <ul class="submenu {{ Request::is('project/3d-struktur*') || Request::is('project/plumbing*') || Request::is('project/rab*') ? 'active' : '' }}">
                        <li class="submenu-item {{ Request::routeIs('project.struktur') ? 'active' : '' }}">
                            <a class="submenu-link" href="{{ route('project.struktur') }}">3D Struktur</a>
                        </li>
                        <li class="submenu-item {{ Request::routeIs('project.plumbing') ? 'active' : '' }}">
                            <a class="submenu-link" href="{{ route('project.plumbing') }}">Skema Plumbing</a>
                        </li>                        
                        <li class="submenu-item {{ Request::routeIs('project.rab') ? 'active' : '' }}">
                            <a class="submenu-link" href="{{ route('project.rab') }}">RAB</a>
                        </li>
                    </ul>
                </li>
                
                <li class="sidebar-item has-sub {{ Request::is('project/mou*') ? 'active' : '' }}">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-diagram-3-fill"></i>
                        <span>Arsitek Project</span>
                    </a>
                    <ul class="submenu {{ Request::is('project/mou*') }}">
                        <li class="submenu-item {{ Request::routeIs('project.mou') ? 'active' : '' }}">
                            <a class="submenu-link" href="{{ route('project.mou') }}">MOU</a>
                        </li>
                    </ul>
                </li>

                    <li class="sidebar-item {{ Request::routeIs('project.proyek') ? 'active' : '' }}">
                        <a href="{{ route('project.proyek') }}" class='sidebar-link'>
                            <i class="bi bi-hourglass-split"></i>
                            <span>Proyek Berjalan</span>
                        </a>    
                    </li>
                <li class="sidebar-item">
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class='sidebar-link'>
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>