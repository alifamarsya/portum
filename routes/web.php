<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PanduanController;
use App\Http\Controllers\RisalahRapatController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/ganti-password-wajib', [LoginController::class, 'forceChangeForm'])->name('password.force-change');
    Route::post('/ganti-password-wajib', [LoginController::class, 'forceChange'])->name('password.force-change.submit');

    Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analitik', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('analitik');
    Route::get('/analitik/biaya/{kategori}', [App\Http\Controllers\AnalyticsController::class, 'detailKategori'])
    ->name('analitik.detail-kategori');

    // Mesin CRUD generik untuk 20 modul (lihat config/modules.php) --
    // setara routing dinamis {resource}?action=... di portum.py.
    Route::prefix('modul/{key}')->name('modul.')->group(function () {
        Route::get('/', [ModuleController::class, 'index'])->name('index');
        Route::get('/tambah', [ModuleController::class, 'create'])->name('create');
        Route::post('/', [ModuleController::class, 'store'])->name('store');
        Route::get('/{id}/ubah', [ModuleController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ModuleController::class, 'update'])->name('update');
        Route::delete('/{id}', [ModuleController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/setujui', [ModuleController::class, 'approve'])->name('approve');
        Route::post('/{id}/tolak', [ModuleController::class, 'reject'])->name('reject');
    });

    Route::get('/panduan', [PanduanController::class, 'index'])->name('panduan.index');
    Route::post('/panduan', [PanduanController::class, 'store'])->name('panduan.store');
    Route::put('/panduan/{panduan}', [PanduanController::class, 'update'])->name('panduan.update');
    Route::delete('/panduan/{panduan}', [PanduanController::class, 'destroy'])->name('panduan.destroy');

    Route::resource('risalah', RisalahRapatController::class)->except(['show']);

    Route::prefix('admin')->name('admin.')->middleware('superadmin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions');

        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
    });
});
