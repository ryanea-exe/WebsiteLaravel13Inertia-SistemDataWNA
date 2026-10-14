<?php

use App\Http\Controllers\AbgController;
use App\Http\Controllers\AffidavitController;
use App\Http\Controllers\DashboardController;
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
});

// Admin-only route group for authorization checks
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', fn () => response()->json(['status' => 'admin_access_granted']))->name('users.index');
});

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';
