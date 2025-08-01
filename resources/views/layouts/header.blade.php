<style>
    .custom-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
        background-color: transparent;
    }

    .custom-header .burger-btn {
        font-size: 1.5rem;
        color: var(--bs-body-color);
    }

    .nav-item.dropdown .dropdown-menu.custom-user-dropdown {
        top: 60px;
        right: 10px;
        left: auto;
        min-width: 240px;
        border: none;
        border-radius: 1rem;
        padding: 1rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        background-color: var(--bs-dropdown-bg);
        color: var(--bs-body-color);
    }

    .custom-user-dropdown .dropdown-item {
        font-size: 0.95rem;
        color: inherit;
        background-color: transparent;
    }

    .custom-user-dropdown .dropdown-item:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .custom-user-dropdown .text-muted {
        color: var(--bs-secondary-color) !important;
    }

    .custom-user-dropdown .user-name {
        font-weight: bold;
        color: var(--bs-body-color);
    }

    .avatar-icon {
        font-size: 1.75rem;
        color: #4fafff;
    }

    .avatar-icon-lg {
        font-size: 2.25rem;
        color: #4fafff;
    }

    /* ========== DARK MODE (Bootstrap 5.3+) ========== */
    html[data-bs-theme="dark"] .dropdown-menu.custom-user-dropdown {
        background-color: #2c2e3e !important;
        color: #ffffff !important;
    }

    html[data-bs-theme="dark"] .dropdown-menu.custom-user-dropdown .dropdown-item {
        color: #ffffff !important;
    }

    html[data-bs-theme="dark"] .dropdown-menu.custom-user-dropdown .dropdown-item:hover {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }

    html[data-bs-theme="dark"] .dropdown-menu.custom-user-dropdown .text-muted {
        color: #a1a1a1 !important;
    }

    html[data-bs-theme="dark"] .dropdown-menu.custom-user-dropdown .user-name {
        color: #ffffff !important;
    }

    /* ========== LIGHT MODE ========== */
    html[data-bs-theme="light"] .dropdown-menu.custom-user-dropdown {
        background-color: #ffffff !important;
        color: #212529 !important;
    }

    html[data-bs-theme="light"] .dropdown-menu.custom-user-dropdown .dropdown-item {
        color: #212529 !important;
    }

    html[data-bs-theme="light"] .dropdown-menu.custom-user-dropdown .dropdown-item:hover {
        background-color: rgba(0, 0, 0, 0.05) !important;
    }

    html[data-bs-theme="light"] .dropdown-menu.custom-user-dropdown .text-muted {
        color: #6c757d !important;
    }

    html[data-bs-theme="light"] .dropdown-menu.custom-user-dropdown .user-name {
        color: #212529 !important;
    }
</style>

@php
    use Illuminate\Support\Facades\File;

    $role = Auth::user()->role ?? 'default';
    $path = public_path("mazer/assets/images/faces/{$role}");
    $files = File::exists($path) ? File::files($path) : [];

    $avatar = count($files) > 0 
        ? asset("mazer/assets/images/faces/{$role}/" . $files[array_rand($files)]->getFilename())
        : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name ?? 'User');
@endphp

<header class="custom-header mb-3">
    <!-- Hamburger -->
    <a href="#" class="burger-btn d-block d-xl-none">
        <i class="bi bi-justify fs-3"></i>
    </a>

    <!-- Avatar & Dropdown -->
    <li class="nav-item dropdown ms-auto position-relative list-unstyled">
        <!-- Trigger -->
        <a class="nav-link d-flex align-items-center gap-2 pe-2" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="{{ $avatar }}" class="rounded-circle border border-2" width="36" height="36" alt="User Avatar" style="object-fit: cover;">
            <span class="fw-semibold small d-none d-md-inline" style="color: var(--bs-body-color);">{{ Auth::user()->name }}</span>
        </a>

        <!-- Dropdown Menu -->
        <ul class="dropdown-menu custom-user-dropdown">
            <!-- User Info -->
            <li class="d-flex align-items-center mb-3 px-2">
                <img src="{{ $avatar }}" class="rounded-circle me-3 border border-2" width="48" height="48" alt="User Avatar" style="object-fit: cover;">
                <div class="lh-sm">
                    <div class="fw-semibold user-name">{{ Auth::user()->name }}</div>
                    <div class="text-muted small">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
            </li>
            <hr class="text-secondary my-2">
            <!-- Profile -->
            <li>
                <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('profile') }}">
                    <span class="fw-normal">My Profile</span>
                </a>
            </li>
            <!-- Logout -->
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="dropdown-item d-flex align-items-center py-2 text-danger bg-transparent border-0 w-100" type="submit">
                        <span class="fw-normal">Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </li>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function () {
                sidebar.classList.toggle('active');
            });
        }
    });
</script>

