<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\StudioController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AkunController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login'); // ⬅️ Pastikan 'login' adalah nama route halaman login kamu
});

// Redirect setelah login berdasarkan role
Route::middleware(['auth'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/akun', [AkunController::class, 'index'])->name('akun.index');
    Route::get('/akun/{id}', [AkunController::class, 'show'])->name('akun.show');
    Route::post('/akun/reset-password', [AkunController::class, 'resetPassword'])->name('akun.resetPassword');
});

// Route dashboard masing-masing role
Route::middleware(['auth', 'role:admin'])->get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
Route::middleware(['auth', 'role:marketing'])->get('/marketing', [MarketingController::class, 'index'])->name('marketing.dashboard');
Route::middleware(['auth', 'role:studio'])->get('/studio', [StudioController::class, 'index'])->name('studio.dashboard');
Route::middleware(['auth', 'role:project'])->get('/project', [ProjectController::class, 'index'])->name('project.dashboard');

// Profile routes (bawaan breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Group by middleware (auth + role)
Route::middleware(['auth'])->group(function () {
    
    // === Admin Bisa Akses Semua ===
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', [MarketingController::class, 'adminDashboard'])->name('admin.dashboard');
    });

    // Marketing
    Route::middleware(['auth', 'role:admin,marketing'])->prefix('marketing')->group(function () {
        // Dashboard & halaman
        Route::get('/dashboard', [MarketingController::class, 'dashboard'])->name('marketing.dashboard');
        
        // Halaman Klien
        Route::get('/klien', [MarketingController::class, 'klien'])->name('marketing.klien.index');

        // Halaman Laporan
        Route::get('/laporan', [MarketingController::class, 'laporan'])->name('marketing.laporan');

        // === CRUD Klien ===
        Route::post('/klien', [MarketingController::class, 'klienStore'])->name('marketing.klien.store');
        Route::get('/klien/{id}/edit', [MarketingController::class, 'klienEdit'])->name('marketing.klien.edit');
        Route::put('/klien/{id}', [MarketingController::class, 'klienUpdate'])->name('marketing.klien.update');
        Route::delete('/klien/{id}', [MarketingController::class, 'klienDestroy'])->name('marketing.klien.destroy');
        Route::get('/klien/create', [MarketingController::class, 'klienCreate'])->name('klien.create');

    });

    // === Studio ===
    Route::middleware(['role:admin,studio'])->group(function () {
        Route::get('/studio/dashboard', [StudioController::class, 'dashboard'])->name('studio.dashboard');
        Route::get('/studio/klien-survei', [StudioController::class, 'klienSurvei'])->name('studio.kliensurvei');

        Route::get('/studio/denah-moodboard', [StudioController::class, 'denahMoodboard'])->name('studio.denah');
        Route::get('/studio/3d-interior', [StudioController::class, 'interior3D'])->name('studio.3d');
        Route::get('/studio/mep-spek', [StudioController::class, 'mepSpek'])->name('studio.mep');
        Route::get('/studio/tahap-akhir', [StudioController::class, 'tahapAkhir'])->name('studio.akhir');
    });

    // === Project ===
    Route::middleware(['role:admin,project'])->group(function () {
        Route::get('/project/dashboard', [ProjectController::class, 'dashboard'])->name('project.dashboard');
        Route::get('/project/3d-struktur', [ProjectController::class, 'struktur3D'])->name('project.struktur');
        Route::get('/project/plumbing', [ProjectController::class, 'plumbing'])->name('project.plumbing');
        Route::get('/project/rab', [ProjectController::class, 'rab'])->name('project.rab');
        Route::get('/project/mou', [ProjectController::class, 'mou'])->name('project.mou');
        Route::get('/project/proyek', [ProjectController::class, 'proyek'])->name('project.proyek');
    });

    // ==Halaman Profil==
    Route::middleware(['auth'])->group(function () {
        Route::get('/profile', [ProfilController::class, 'index'])->name('profile');
        Route::post('/profil/password', [ProfilController::class, 'updatePassword'])->name('profil.password.update');
    });


});
