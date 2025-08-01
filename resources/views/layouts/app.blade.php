<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TBID</title>

    <!-- Bootstrap Icons (HANYA perlu satu versi, pilih yang terbaru saja) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Bootstrap JS (jangan hanya CSS) -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- Mazer CSS -->
    <link rel="stylesheet" href="{{ asset('mazer/assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/css/app-dark.css') }}">

    <!-- Custom CSS (pastikan custom.css paling bawah agar bisa override) -->
    <link rel="stylesheet" href="{{ asset('mazer/assets/css/custom.css') }}">

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('mazer/assets/images/logo/favicon.svg') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('mazer/assets/images/logo/favicon.png') }}" type="image/png">
    
    
    <style>
        [data-bs-theme="dark"] {
            background-color: #121212;
        }
        /* Optional tambahan styling */
        #sidebar .form-check-input {
            cursor: pointer;
            width: 2.5em;
            height: 1.2em;
        }

        #sidebar .bi-sun-fill,
        #sidebar .bi-moon-fill {
            font-size: 1.2rem;
        }
                .submenu-link {
            color: #ccc;
            transition: all 0.2s ease-in-out;
        }
        .submenu-link:hover {
            color: #fff;
        }
        .submenu-link.text-primary {
            color: #0d6efd !important; /* Bootstrap primary */
        }

        .submenu-item.active > .submenu-link {
            color: #0d6efd !important;  /* Bootstrap Primary */
            font-weight: bold;
        }

        .dropdown-menu {
            background-color: #2c2e3e !important;
            color: #fff;
        }

        .dropdown-menu .dropdown-item {
            color: #ccc;
            transition: background-color 0.2s;
        }

        .dropdown-menu .dropdown-item:hover {
            background-color: #3c3e4e;
            color: #fff;
        }

        .dropdown-menu {
            transition: all 0.2s ease-in-out;
        }
        html[data-bs-theme="light"] div#sidebar {
            background-color: #f1f3f4 !important;
        }

        html[data-bs-theme="dark"] div#sidebar {
            background-color: #1e1e2f !important;
        }
        .swal2-custom-popup {
            border-radius: 1rem;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
        }

        .swal2-custom-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin-top: 1rem;
        }

        .swal2-custom-content {
            font-size: 1rem;
            margin-top: 0.5rem;
        }



    </style>
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        feather.replace();
    </script>
    </head>
    <body>


    <div id="app">
        @include('layouts.sidebar')

        <div id="main" class='layout-navbar'>
            @include('layouts.header')

            <div id="main-content">
                <div class="page-heading">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('mazer/assets/js/bootstrap.js') }}"></script>
    <script src="{{ asset('mazer/assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const html = document.querySelector('html');
            const themeToggle = document.getElementById('theme-toggle');

            // Set default dari localStorage
            const savedTheme = localStorage.getItem('bs-theme') || 'light';
            html.setAttribute('data-bs-theme', savedTheme);
            if (themeToggle) themeToggle.checked = savedTheme === 'dark';

            // Toggle theme
            themeToggle?.addEventListener('change', function () {
                const newTheme = themeToggle.checked ? 'dark' : 'light';
                html.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('bs-theme', newTheme);
            });

            // Inisialisasi DataTable jika ada
            if (typeof $ !== 'undefined' && $('#akunTable').length) {
                $('#akunTable').DataTable();
            }

            // Sembunyikan alert sukses setelah 3 detik
            const alert = document.getElementById('success-alert');
            if (alert) {
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 3000);
            }

            // Fungsi buka modal reset password
            window.openResetModal = function (id, name) {
                document.getElementById('resetUserId').value = id;
                document.getElementById('resetUserName').textContent = '(' + name + ')';
                const modalElement = document.getElementById('resetPasswordModal');
                const modalInstance = new bootstrap.Modal(modalElement);
                modalInstance.show();
            }
        });
    document.querySelectorAll('.sidebar-item.has-sub > a').forEach(function (element) {
        element.addEventListener('click', function (e) {
            e.preventDefault();
            let parent = element.closest('.sidebar-item');
            parent.classList.toggle('active');
        });
    });
    feather.replace();
</script>
<script src="{{ asset('mazer/vendors/simple-datatables/simple-datatables.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.querySelector('#table1');
        if (table) {
            new simpleDatatables.DataTable(table, {
                searchable: true,
                fixedHeight: true
            });
        }
    });
</script>


    <script>feather.replace();</script>
        <!-- Tag meta, link CSS, dsb... -->
    <link rel="stylesheet" href="{{ asset('mazer/assets/css/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">
</body>
</html>
