<?php

use App\Http\Controllers\AbgController;
use App\Http\Controllers\AffidavitController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataUserController;
use App\Http\Controllers\EarlyWarningAbgController;
use App\Http\Controllers\EarlyWarningWnaController;
use App\Http\Controllers\LaporanIzinTinggalController;
use App\Http\Controllers\PenerbitanController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\RealisasiPnbpController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Controllers\WnaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Modul Data WNA (static routes defined before resource to prevent /wna/{wna} collision)
    Route::get('/wna/export', [WnaController::class, 'export'])->name('wna.export');
    Route::get('/wna/print-list', [WnaController::class, 'printList'])->name('wna.print-list');
    Route::get('/wna/export-pdf', [WnaController::class, 'printList'])->name('wna.export-pdf');
    Route::get('/wna/{wna}/card', [WnaController::class, 'printCard'])->name('wna.card');
    Route::get('/wna/{wna}/photo', [WnaController::class, 'photo'])->name('wna.photo');
    Route::resource('wna', WnaController::class);

    // Modul Pendaftaran ABG (static routes defined before resource to prevent /abg/{abg} collision)
    Route::get('/abg/export', [AbgController::class, 'export'])->name('abg.export');
    Route::get('/abg/print-list', [AbgController::class, 'printList'])->name('abg.print-list');
    Route::get('/abg/export-pdf', [AbgController::class, 'printList'])->name('abg.export-pdf');
    Route::get('/abg/{abg}/photo', [AbgController::class, 'photo'])->name('abg.photo');
    Route::resource('abg', AbgController::class);

    // Modul Pendaftaran Affidavit (static routes defined before resource to prevent /affidavit/{affidavit} collision)
    Route::get('/affidavit/export', [AffidavitController::class, 'export'])->name('affidavit.export');
    Route::get('/affidavit/print-list', [AffidavitController::class, 'printList'])->name('affidavit.print-list');
    Route::get('/affidavit/export-pdf', [AffidavitController::class, 'printList'])->name('affidavit.export-pdf');
    Route::get('/affidavit/{affidavit}/photo', [AffidavitController::class, 'photo'])->name('affidavit.photo');
    Route::resource('affidavit', AffidavitController::class);

    // Modul Early Warning WNA
    Route::get('/early-warning-wna/print-list', [EarlyWarningWnaController::class, 'printList'])->name('early-warning-wna.print-list');
    Route::get('/early-warning-wna/export-pdf', [EarlyWarningWnaController::class, 'printList'])->name('early-warning-wna.export-pdf');
    Route::get('/early-warning-wna', [EarlyWarningWnaController::class, 'index'])->name('early-warning-wna.index');

    Route::get('/early-warning/print-list', [EarlyWarningWnaController::class, 'printList'])->name('early-warning.print-list');
    Route::get('/early-warning/export-pdf', [EarlyWarningWnaController::class, 'printList'])->name('early-warning.export-pdf');
    Route::get('/early-warning', [EarlyWarningWnaController::class, 'index'])->name('early-warning.index');

    // Modul Early Warning ABG
    Route::get('/early-warning/abg/print-list', [EarlyWarningAbgController::class, 'printList'])->name('early-warning-abg.print-list');
    Route::get('/early-warning/abg/export-pdf', [EarlyWarningAbgController::class, 'printList'])->name('early-warning-abg.export-pdf');
    Route::get('/early-warning/abg', [EarlyWarningAbgController::class, 'index'])->name('early-warning-abg.index');

    // Modul Permohonan Keimigrasian (Khusus Admin)
    Route::get('/permohonan/print-list', [PermohonanController::class, 'printList'])->name('permohonan.print-list');
    Route::get('/permohonan/export-pdf', [PermohonanController::class, 'printList'])->name('permohonan.export-pdf');
    Route::resource('permohonan', PermohonanController::class);

    // Modul Laporan Izin Tinggal (Khusus Admin)
    Route::get('/laporan-izin-tinggal/print-list', [LaporanIzinTinggalController::class, 'printList'])->name('laporan-izin-tinggal.print-list');
    Route::get('/laporan-izin-tinggal/export-pdf', [LaporanIzinTinggalController::class, 'printList'])->name('laporan-izin-tinggal.export-pdf');
    Route::resource('laporan-izin-tinggal', LaporanIzinTinggalController::class);

    // Modul Penerbitan (Khusus Admin)
    Route::get('/penerbitan/print-list', [PenerbitanController::class, 'printList'])->name('penerbitan.print-list');
    Route::get('/penerbitan/export-pdf', [PenerbitanController::class, 'printList'])->name('penerbitan.export-pdf');
    Route::resource('penerbitan', PenerbitanController::class);

    // Modul Realisasi PNBP (Khusus Admin)
    Route::get('/realisasi-pnbp/print-list', [RealisasiPnbpController::class, 'printList'])->name('realisasi-pnbp.print-list');
    Route::get('/realisasi-pnbp/export-pdf', [RealisasiPnbpController::class, 'printList'])->name('realisasi-pnbp.export-pdf');
    Route::resource('realisasi-pnbp', RealisasiPnbpController::class);

    // Modul Data User (Khusus Admin)
    Route::get('/data-user/{user}/photo', [DataUserController::class, 'photo'])->name('data-user.photo');
    Route::resource('data-user', DataUserController::class)->parameters(['data-user' => 'user']);

    // Modul Pengaturan Aplikasi (Khusus Admin)
    Route::get('/pengaturan', [SettingController::class, 'index'])->name('pengaturan.index');
    Route::post('/pengaturan', [SettingController::class, 'update'])->name('pengaturan.update');
});

// Endpoint streaming publik ikon aplikasi (mendukung slot 1, 2, 3 atau logo utama)
Route::get('/pengaturan/icon/{slot?}', [SettingController::class, 'icon'])
    ->where('slot', '[1-3](\.[a-zA-Z0-9]+)?')
    ->name('settings.icon');

// Admin-only route group for authorization checks
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', fn () => response()->json(['status' => 'admin_access_granted']))->name('users.index');
});

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';
