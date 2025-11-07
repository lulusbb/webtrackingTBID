<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\StudioController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\Marketing\DashboardMarketingController;
use App\Http\Controllers\Project\ProjectDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Admin\SettingsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ========== Root & Auth ==========
Route::get('/', fn () => redirect()->route('login'));
require __DIR__.'/auth.php';

// ---------- Fallback setelah login ----------
Route::middleware('auth')->get('/dashboard', function () {
    $role = strtolower(auth()->user()->role ?? '');
    return redirect()->to(match ($role) {
        'admin'     => route('admin.dashboard'),
        'marketing' => route('marketing.dashboard'),
        'studio'    => route('studio.dashboard'),
        'project'   => route('project.dashboard'),
        'ceo'       => route('admin.dashboard'), // CEO lihat Dashboard Utama (read-only)
        default     => url('/'),
    });
})->name('dashboard');

// ---------- Logout ----------
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// ---------- CEO (alias read-only) ----------
Route::middleware(['auth','role:ceo'])->group(function () {
    Route::get('/ceo/dashboard', fn () => redirect()->route('admin.dashboard'))->name('ceo.dashboard');
    Route::get('/ceo/proyek-selesai', fn () => redirect()->route('project.selesai.index'))->name('ceo.proyekSelesai');
});

Route::middleware('auth')->group(function () {
    // Feed custom (union dari banyak tabel) + tandai dilihat (session)
    Route::get('/notifications/feed',  [NotificationController::class, 'feed'])->name('notifications.feed');
    Route::post('/notifications/seen', [NotificationController::class, 'markSeen'])->name('notifications.seen');

    // Laravel built-in notifications (pakai tabel `notifications`)
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])
        ->name('notifications.read_all');

    // WAJIB: param harus {notification} agar route model binding ke DatabaseNotification bekerja
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'readOne'])
        ->name('notifications.read_one');

    // Chat antar role
    Route::get('/chat',            [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/feed',       [ChatController::class, 'feed'])->name('chat.feed');
    Route::get('/chat/unread',     [ChatController::class, 'unread'])->name('chat.unread');
    Route::post('/chat/send',      [ChatController::class, 'send'])->name('chat.send');
    Route::post('/chat/seen',      [ChatController::class, 'markSeen'])->name('chat.seen');
    Route::get('/chat/history',    [ChatController::class, 'history'])->name('chat.history');

    // Alias agar cocok dengan JS lama
    Route::get('/chat/fetch',      [ChatController::class, 'feed'])->name('chat.fetch');
    Route::post('/chat/mark-seen', [ChatController::class, 'markSeen'])->name('chat.markSeen');
});


Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])
        ->middleware('role:admin,ceo')->name('admin.dashboard');

    Route::get('/pengaturan', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/pengaturan/thresholds', [SettingsController::class, 'updateThresholds'])->name('settings.thresholds.update');
    Route::post('/pengaturan/reset-db', [SettingsController::class, 'resetDatabase'])->name('settings.resetdb');
    Route::middleware('auth')->get('/admin/data', [\App\Http\Controllers\AdminController::class, 'dashboardData'])->name('admin.dashboard.data');

    Route::middleware('role:admin')->as('admin.')->group(function () {
        Route::get('/akun', [AkunController::class, 'index'])->name('akun.index');
        Route::get('/akun/{id}', [AkunController::class, 'show'])->name('akun.show');
        Route::post('/akun/reset-password', [AkunController::class, 'resetPassword'])->name('akun.resetPassword');
        
    });
});

// ========== Route dashboard masing-masing role ==========
Route::middleware(['auth','role:marketing'])->get('/marketing', [MarketingController::class, 'index'])->name('marketing.dashboard');
Route::middleware(['auth','role:studio'])->get('/studio',       [StudioController::class,   'index'])->name('studio.dashboard');
Route::middleware(['auth','role:project'])->get('/project',     [ProjectController::class,  'index'])->name('project.dashboard');

// ========== Profile ==========
Route::middleware('auth')->group(function () {
    Route::get('/profile',   [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tambahkan route untuk ubah password (samakan dengan yang dipakai di blade)
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
         ->name('profil.password.update');   // <-- sesuai form di blade
});


// ========== MARKETING ==========
Route::middleware(['auth','role:admin,marketing'])
    ->prefix('marketing')->name('marketing.')->group(function () {

    Route::get('/', fn () => redirect()->route('marketing.dashboard'))->name('home');
    Route::get('/dashboard',       [DashboardMarketingController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardMarketingController::class, 'stats'])->name('dashboard.stats');

    Route::get('/klien',        [MarketingController::class, 'klien'])->name('klien.index');
    Route::get('/klien/data',   [MarketingController::class, 'klienData'])->name('klien.data');
    Route::get('/klien/create', [MarketingController::class, 'klienCreate'])->name('klien.create');
    Route::post('/klien',       [MarketingController::class, 'klienStore'])->name('klien.store');
    Route::get('/klien/{id}',   [MarketingController::class, 'show'])->name('klien.show');
    Route::get('/klien/{id}/edit', [MarketingController::class, 'klienEdit'])->name('klien.edit');
    Route::put('/klien/{id}',      [MarketingController::class, 'klienUpdate'])->name('klien.update');
    Route::delete('/klien/{id}',   [MarketingController::class, 'klienDestroy'])->name('klien.destroy');

    Route::delete('/klien/{id}/cancel', [MarketingController::class, 'cancel'])->name('klien.cancel');
    Route::get('/klien_cancel/{id}',    [MarketingController::class, 'showKlienCancel'])->name('klien_cancel.show');

    Route::get('/klien-cancelled',      [MarketingController::class, 'klienCancelled'])->name('klien_cancelled.index');
    Route::get('/klien-cancelled/data', [MarketingController::class, 'klienCancelledData'])->name('klien_cancelled.data');

    Route::get('/laporan', [MarketingController::class, 'laporan'])->name('laporan');
    Route::get('/laporan/klien/export',        [MarketingController::class, 'exportKliens'])->name('laporan.klien.export');
    Route::get('/laporan/klien-cancel/export', [MarketingController::class, 'exportKliensCancel'])->name('laporan.klien_cancel.export');
    Route::get('/laporan/klien/data',          [MarketingController::class, 'klienBaruDataLaporan'])->name('laporan.klien.data');
    Route::get('/laporan/klien-cancel/data',   [MarketingController::class, 'klienCancelDataLaporan'])->name('laporan.klien_cancel.data');

    Route::post('/klien/{id}/send-to-survey',[MarketingController::class,'sendToSurvey'])->name('klien.sendToSurvey');
});


// ========== STUDIO ==========
Route::middleware(['auth','role:admin,studio'])
    ->prefix('studio')->name('studio.')->group(function () {

    Route::get('/dashboard',       [\App\Http\Controllers\Studio\StudioDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [\App\Http\Controllers\Studio\StudioDashboardController::class, 'stats'])->name('dashboard.stats');

    Route::get('/klien-survei',        [StudioController::class, 'klienSurvei'])->name('kliensurvei');
    Route::get('/klien-survei/data',   [StudioController::class, 'klienSurveiData'])->name('kliensurvei.data');
    Route::get('/klien-survei/{id}',   [StudioController::class, 'klienSurveiShow'])->name('kliensurvei.show');

    Route::get('/survei-inbox',                 [StudioController::class, 'surveiInboxIndex'])->name('survei_inbox.index');
    Route::get('/survei-inbox/data',            [StudioController::class, 'surveiInboxData'])->name('survei_inbox.data');
    Route::get('/survei-inbox/{req}',           [StudioController::class, 'surveiInboxShow'])->name('survei_inbox.show');
    Route::post('/survei-inbox/{req}/approve',  [StudioController::class, 'surveiInboxApprove'])->name('survei_inbox.approve');
    Route::post('/survei-inbox/{req}/reject',   [StudioController::class, 'surveiInboxReject'])->name('survei_inbox.reject');
    Route::post('/survei-inbox/{req}/schedule', [StudioController::class, 'scheduleSurvey'])->name('survei_inbox.schedule');

    Route::get ('/survei-scheduled/data',            [StudioController::class, 'surveiScheduledData'])->name('survei_scheduled.data');
    Route::get ('/survei/scheduled/{fix}',           [StudioController::class, 'surveiScheduledShow'])->name('survei_scheduled.show');
    Route::patch('/survei/scheduled/{fix}/catatan',  [StudioController::class, 'updateScheduledNote'])->name('survei_scheduled.note');
    Route::patch('/survei/scheduled/{fix}/lembar',   [StudioController::class, 'updateSurveySheet'])->name('survei_scheduled.lembar');
    Route::post ('/survei/scheduled/{fix}/to-denah', [StudioController::class, 'moveToDenah'])->name('survei_scheduled.to_denah');
    Route::post ('/survei/scheduled/{fix}/cancel',   [StudioController::class, 'cancelScheduled'])->name('survei_scheduled.cancel');
    Route::post ('/survei/scheduled/{fix}/done',     [StudioController::class, 'markSurveyDone'])->name('survei_scheduled.done');

    Route::get('/denah-moodboard',             [StudioController::class, 'denahMoodboard'])->name('denah');
    Route::get('/denah-moodboard/data',        [StudioController::class, 'denahData'])->name('denah.data');
    Route::get('/denah-moodboard/{denah}',     [StudioController::class, 'denahShow'])->name('denah.show');
    Route::get('/denah-moodboard/cancel/data', [StudioController::class, 'denahCancelData'])->name('denah_cancel.data');
    Route::get('/denah-moodboard/cancel/{id}', [StudioController::class, 'denahCancelShow'])->name('denah_cancel.show');
    Route::post('/denah/{denah}/cancel',       [StudioController::class, 'cancelDenah'])->name('denah.cancel');
    Route::post('/denah/{denah}/to-exterior',  [StudioController::class, 'moveDenahToExterior'])->name('denah.to_exterior');

    Route::get('/survei/cancel',      [StudioController::class, 'surveiCancelIndex'])->name('survei_cancel.index');
    Route::get('/survei/cancel/data', [StudioController::class, 'surveiCancelData'])->name('survei_cancel.data');
    Route::get('/survei/cancel/{id}', [StudioController::class, 'surveiCancelShow'])->name('survei_cancel.show');

    Route::get('/3d-exterior-interior', [StudioController::class, 'exteriorsIndex'])->name('3d');

    Route::prefix('exteriors')->name('exteriors.')->group(function () {
        Route::get('/',            [StudioController::class, 'exteriorsIndex'])->name('index');
        Route::get('/data',        [StudioController::class, 'exteriorsData'])->name('data');
        Route::get('/cancel/data', [StudioController::class, 'exteriorsCancelData'])->name('cancel_data');
        Route::get('/{exterior}',  [StudioController::class, 'exteriorsShow'])->whereNumber('exterior')->name('show');
        Route::post('/{exterior}/to-mep', [StudioController::class, 'moveExteriorToMep'])->whereNumber('exterior')->name('to_mep');
        Route::post('/{exterior}/cancel', [StudioController::class, 'cancelExterior'])->whereNumber('exterior')->name('cancel');
    });

    Route::get('/mep-spek',              [StudioController::class, 'mepIndex'])->name('mep');
    Route::get('/mep-spek/data',         [StudioController::class, 'mepData'])->name('mep.data');
    Route::get('/mep-spek/cancel/data',  [StudioController::class, 'mepCancelData'])->name('mep_cancel.data');
    Route::get('/mep-spek/{mep}',        [StudioController::class, 'mepShow'])->name('mep.show');
    Route::post('/mep-spek/{mep}/to-struktur', [StudioController::class, 'moveMepToStruktur'])->name('mep.to_struktur');
    Route::post('/mep-spek/{mep}/cancel',      [StudioController::class, 'cancelMep'])->whereNumber('mep')->name('mep.cancel');

    Route::get('/tahap-akhir',          [StudioController::class,'tahapAkhirIndex'])->name('akhir');
    Route::get('/tahap-akhir/data',     [StudioController::class,'tahapAkhirData'])->name('akhir.data');
    Route::get('/tahap-akhir/{akhir}',  [StudioController::class,'tahapAkhirShow'])->whereNumber('akhir')->name('akhir.show');
    Route::post('/tahap-akhir/{akhir}/serter-selesai', [StudioController::class,'akhirSerterSelesai'])->whereNumber('akhir')->name('akhir.serter_selesai');

    // Delegasi RAB (nama konsisten: studio.delegasirab.*)
    Route::get('delegasi-rab',              [StudioController::class, 'delegasiRabIndex'])->name('delegasirab.index');
    Route::get('delegasi-rab/data',         [StudioController::class, 'delegasiRabData'])->name('delegasirab.data');
    Route::post('delegasi-rab/move/{mep}',  [StudioController::class, 'moveMepToDelegasi'])->whereNumber('mep')->name('delegasirab.move');
    Route::get('delegasi-rab/{delegasiRab}',[StudioController::class, 'delegasiRabShow'])->name('delegasirab.show');
    Route::post('delegasi-rab/{delegasiRab}/lanjut', [StudioController::class, 'delegasiRabLanjut'])->name('delegasirab.lanjut');

    Route::get('/exteriors/{exterior}/edit', [StudioController::class, 'exteriorEdit'])->whereNumber('exterior')->name('exteriors.edit');
    Route::put('/exteriors/{exterior}',      [StudioController::class, 'exteriorUpdate'])->whereNumber('exterior')->name('exteriors.update');

    Route::post('/tahap-akhir/{akhir}/to-mou', [StudioController::class,'akhirToMou'])->name('akhir.to_mou');
    Route::post('/tahap-akhir/{akhir}/cancel', [StudioController::class,'akhirCancel'])->name('akhir.cancel');
});

// Endpoint penerimaan “Lanjut Survei” (Marketing → Studio)
Route::post('/studio/survei-inbox/{klien}', [StudioController::class, 'surveiInboxStore'])
    ->middleware(['auth','role:admin,marketing,studio'])
    ->whereNumber('klien')
    ->name('studio.survei_inbox.store');


// ========== PROJECT ==========
Route::prefix('project')->as('project.')->middleware('auth')->group(function () {

    /* ===== KELOLA (admin & project) ===== */
    Route::middleware('role:admin,project')->group(function () {
        Route::get('/dashboard',       [ProjectDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [ProjectDashboardController::class, 'stats'])->name('dashboard.stats');

        Route::get('/proyek',               [ProjectController::class, 'proyekIndex'])->name('proyek.index');
        Route::get('/proyek/data',          [ProjectController::class, 'proyekData'])->name('proyek.data');
        Route::get('/proyek/{proyek}',      [ProjectController::class, 'proyekShow'])->whereNumber('proyek')->name('proyek.show');
        Route::get('/proyek/{proyek}/edit', [ProjectController::class, 'proyekEdit'])->whereNumber('proyek')->name('proyek.edit');
        Route::match(['put','patch'], '/proyek/{proyek}', [ProjectController::class, 'proyekUpdate'])->whereNumber('proyek')->name('proyek.update');
        Route::post('/proyek/{proyek}/selesai', [ProjectController::class, 'selesai'])->whereNumber('proyek')->name('proyek.selesai');

        Route::get('/struktur3d',                 [ProjectController::class, 'struktur3dIndex'])->name('struktur3d.index');
        Route::get('/struktur3d/data',            [ProjectController::class, 'struktur3dData'])->name('struktur3d.data');
        Route::get('/struktur3d/cancel/data',     [ProjectController::class, 'struktur3dCancelData'])->name('struktur3d.cancel_data');
        Route::post('/struktur3d/{struktur3d}/to-skema', [ProjectController::class, 'toSkema'])->whereNumber('struktur3d')->name('struktur3d.to_skema');
        Route::post('/struktur3d/{struktur3d}/cancel',   [ProjectController::class, 'cancelStruktur3d'])->whereNumber('struktur3d')->name('struktur3d.cancel');
        Route::get('/struktur3d/{struktur3d}',   [ProjectController::class, 'struktur3dShow'])->whereNumber('struktur3d')->name('struktur3d.show');

        Route::get('/skema',                 [ProjectController::class, 'skemaIndex'])->name('skema.index');
        Route::get('/skema/data',            [ProjectController::class, 'skemaData'])->name('skema.data');
        Route::get('/skema/cancel/data',     [ProjectController::class, 'skemaCancelData'])->name('skema.cancel_data');
        Route::post('/skema/{skema}/to-rab', [ProjectController::class, 'toRab'])->whereNumber('skema')->name('skema.to_rab');
        Route::post('/skema/{skema}/cancel', [ProjectController::class, 'cancelSkema'])->whereNumber('skema')->name('skema.cancel');
        Route::get('/skema/{skema}',         [ProjectController::class, 'skemaShow'])->whereNumber('skema')->name('skema.show');
        Route::get('/skema_cancel/data',     [ProjectController::class, 'skemaCancelData'])->name('skema_cancel.data');

        Route::get('/rab',                [ProjectController::class, 'rabIndex'])->name('rab.index');
        Route::get('/rab/data',           [ProjectController::class, 'rabData'])->name('rab.data');
        Route::get('/rab/cancel-data',    [ProjectController::class, 'rabCancelData'])->name('rab.cancel_data');
        Route::get('/rab/{rab}',          [ProjectController::class, 'rabShow'])->whereNumber('rab')->name('rab.show');
        Route::post('/rab/{rab}/cancel',  [ProjectController::class, 'cancelRab'])->whereNumber('rab')->name('rab.cancel');
        Route::post('/rab/{rab}/to-akhir',[ProjectController::class, 'toAkhir'])->whereNumber('rab')->name('rab.to_akhir');
        Route::post('/rab/{rab}/to-mou',  [ProjectController::class, 'toMou'])->whereNumber('rab')->name('rab.to_mou');

        Route::get('/mou',       [ProjectController::class, 'mouIndex'])->name('mou.index');
        Route::get('/mou/data',  [ProjectController::class, 'mouData'])->name('mou.data');
        Route::get('/mou/{mou}', [ProjectController::class, 'mouShow'])->whereNumber('mou')->name('mou.show');
        Route::post('/mou/{mou}/to-proyekjalan', [ProjectController::class, 'mouToProyekJalan'])->whereNumber('mou')->name('mou.to_proyekjalan');

        Route::patch('/selesai/{proyekselesaii}/keterangan', [ProjectController::class, 'selesaiUpdateKeterangan'])->name('selesai.keterangan.update');
    });

    /* ===== TAMPIL (admin, project, ceo) ===== */
    Route::middleware('role:admin,project,ceo')->group(function () {
        Route::get('/selesai',                  [ProjectController::class, 'selesaiIndex'])->name('selesai.index');
        Route::get('/selesai/data',             [ProjectController::class, 'selesaiData'])->name('selesai.data');
        Route::get('/selesai/{proyekselesaii}', [ProjectController::class, 'selesaiShow'])->name('selesai.show');
    });
});
