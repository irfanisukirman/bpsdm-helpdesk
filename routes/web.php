<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Public\TicketController as PublicTicketController;
use App\Http\Controllers\Public\TrackingController;
use App\Http\Controllers\SuperAdmin\CategoryController;
use App\Http\Controllers\SuperAdmin\SubcategoryController;
use App\Http\Controllers\SuperAdmin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Publik (tanpa login) — PRD Bagian 5.1, 5.9, 5.10
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/tiket/baru', [PublicTicketController::class, 'create'])->name('tickets.create');
Route::post('/tiket', [PublicTicketController::class, 'store'])
    ->middleware('throttle:15,1') // pembatasan laju anti-spam (Bagian 8)
    ->name('tickets.store');
Route::get('/tiket/terkirim', [PublicTicketController::class, 'success'])->name('tickets.success');

Route::get('/lacak', [TrackingController::class, 'form'])->name('track.form');
Route::post('/lacak', [TrackingController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('track.show');
Route::post('/lacak/buka-kembali', [TrackingController::class, 'reopen'])
    ->middleware('throttle:15,1')
    ->name('track.reopen');

/*
|--------------------------------------------------------------------------
| Autentikasi Admin — PRD Bagian 3
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [LoginController::class, 'create'])->name('login');
    Route::post('/admin/login', [LoginController::class, 'store'])->middleware('throttle:20,1');
});
Route::post('/admin/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Area Internal (perlu login) — PRD Bagian 7
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Penanganan tiket (cakupan & aksi dibatasi Policy)
    Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/export/{format}', [AdminTicketController::class, 'export'])
        ->where('format', 'csv|pdf')->name('tickets.export');
    Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/process', [AdminTicketController::class, 'process'])->name('tickets.process');
    Route::post('/tickets/{ticket}/resolve', [AdminTicketController::class, 'resolve'])->name('tickets.resolve');
    Route::post('/tickets/{ticket}/notes', [AdminTicketController::class, 'storeNote'])->name('tickets.notes');
    Route::post('/tickets/{ticket}/redistribute', [AdminTicketController::class, 'redistribute'])->name('tickets.redistribute');
    Route::get('/tickets/{ticket}/attachments/{attachment}', [AdminTicketController::class, 'downloadAttachment'])
        ->name('tickets.attachments.download');

    // Pengelolaan oleh Super Admin (PRD Bagian 7.2)
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('categories', CategoryController::class)->except('show');

        Route::get('/subcategories', [SubcategoryController::class, 'index'])->name('subcategories.index');
        Route::post('/subcategories', [SubcategoryController::class, 'store'])->name('subcategories.store');
        Route::put('/subcategories/{subcategory}', [SubcategoryController::class, 'update'])->name('subcategories.update');
        Route::delete('/subcategories/{subcategory}', [SubcategoryController::class, 'destroy'])->name('subcategories.destroy');

        Route::resource('users', UserController::class)->except('show');
    });
});
