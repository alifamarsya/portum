<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CustomFieldController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TicketConfigController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DisposalAsetController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\MutasiAsetController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OperatorDashboardController;
use App\Http\Controllers\PanduanController;
use App\Http\Controllers\PimpinanDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RisalahRapatController;
use App\Http\Controllers\TicketCategoryController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/ganti-password-wajib', [LoginController::class, 'forceChangeForm'])->name('password.force-change');
    Route::post('/ganti-password-wajib', [LoginController::class, 'forceChange'])->name('password.force-change.submit');

    // Profile routes
    Route::get('/profil', [ProfileController::class, 'showProfile'])->name('profile.show');
    Route::get('/profil/ubah-password', [ProfileController::class, 'showChangePassword'])->name('profile.change-password');
    Route::post('/profil/ubah-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');

    // Notification routes
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifikasi/{id}/baca', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifikasi/{id}/baca', [NotificationController::class, 'read'])->name('notifications.mark-read');
    Route::post('/notifikasi/tandai-semua-dibaca', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/user/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
    Route::get('/operator/dashboard', [OperatorDashboardController::class, 'index'])->name('operator.dashboard');
    Route::get('/kabag/dashboard', [App\Http\Controllers\KabagDashboardController::class, 'index'])->name('kabag.dashboard');
    Route::get('/pimpinan/dashboard', [PimpinanDashboardController::class, 'index'])->name('pimpinan.dashboard');
    Route::get('/staf-umum/dashboard', [App\Http\Controllers\StafUmumDashboardController::class, 'index'])->name('staf-umum.dashboard');
    Route::get('/staf-aset/dashboard', [App\Http\Controllers\StafAsetDashboardController::class, 'index'])->name('staf-aset.dashboard');
    Route::get('/staf-pengadaan/dashboard', [App\Http\Controllers\StafPengadaanDashboardController::class, 'index'])->name('staf-pengadaan.dashboard');
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

    // API Kategori Tiket untuk Form Dropdown
    Route::get('/api/ticket-categories', [TicketCategoryController::class, 'getCategoriesByJenis'])->name('api.ticket-categories');

    // Sistem Tiket
    Route::resource('tickets', TicketController::class);
    Route::get('/tickets/{ticket}/attachment', [TicketController::class, 'viewAttachment'])->name('tickets.attachment');
    Route::get('/tickets/{ticket}/attachment/download', [TicketController::class, 'downloadAttachment'])->name('tickets.attachment.download');
    Route::get('/tickets/attachments/{attachment}/view', [TicketController::class, 'viewAttachmentFile'])->name('tickets.attachment.view-file');
    Route::get('/tickets/attachments/{attachment}/download', [TicketController::class, 'downloadAttachmentFile'])->name('tickets.attachment.download-file');
    Route::post('/tickets/{ticket}/accept', [TicketController::class, 'accept'])->name('tickets.accept');
    Route::post('/tickets/{ticket}/add-progress', [TicketController::class, 'addProgress'])->name('tickets.add-progress');
    Route::post('/tickets/{ticket}/complete', [TicketController::class, 'complete'])->name('tickets.complete');
    Route::post('/tickets/{ticket}/dispose', [TicketController::class, 'dispose'])->name('tickets.dispose');
    Route::post('/tickets/{ticket}/reject', [TicketController::class, 'reject'])->name('tickets.reject');
    Route::post('/tickets/{ticket}/confirm-close', [TicketController::class, 'confirmClose'])
        ->name('tickets.confirm-close');
    Route::post('/tickets/{ticket}/report-incomplete', [TicketController::class, 'reportIncomplete'])
        ->name('tickets.report-incomplete');

    // Modul Mutasi Aset (Pengajuan & Monitoring)
    Route::prefix('mutasi-aset')->name('mutasi-aset.')->group(function () {
        Route::get('/', [MutasiAsetController::class, 'index'])->name('index');
        Route::get('/create', [MutasiAsetController::class, 'create'])->name('create');
        Route::post('/', [MutasiAsetController::class, 'store'])->name('store');
        Route::get('/{mutasi}', [MutasiAsetController::class, 'show'])->name('show');
        Route::get('/{mutasi}/dokumen', [MutasiAsetController::class, 'viewDokumen'])->name('dokumen');
        Route::get('/{mutasi}/dokumen/download', [MutasiAsetController::class, 'downloadDokumen'])->name('dokumen.download');
        Route::post('/{mutasi}/check-operator', [MutasiAsetController::class, 'checkOperator'])->name('check-operator');
        Route::post('/{mutasi}/verifikasi-aset', [MutasiAsetController::class, 'verifikasiBagianAset'])->name('verifikasi-aset');
        Route::post('/{mutasi}/verify-staf', [MutasiAsetController::class, 'verifyStaf'])->name('verify-staf');
        Route::post('/{mutasi}/approve-kabag', [MutasiAsetController::class, 'approveKabag'])->name('approve-kabag');
        Route::post('/{mutasi}/konfirmasi-pengaju', [MutasiAsetController::class, 'konfirmasiPengaju'])->name('konfirmasi-pengaju');
    });

    // Penghapusan Aset / Disposal (Pengajuan, Approval Kadiv, dan Riwayat Terhapus)
    Route::prefix('disposal-aset')->name('disposal-aset.')->group(function () {
        Route::get('/riwayat', [DisposalAsetController::class, 'riwayatTerhapus'])->name('riwayat');
        Route::get('/approval', [DisposalAsetController::class, 'approvalQueue'])->name('approval');
        Route::get('/ajukan/{aset}', [DisposalAsetController::class, 'ajukanForm'])->name('ajukan');
        Route::post('/ajukan/{aset}', [DisposalAsetController::class, 'ajukanSubmit'])->name('ajukan.submit');
        Route::post('/{disposal}/approve', [DisposalAsetController::class, 'approve'])->name('approve');
        Route::post('/{disposal}/reject', [DisposalAsetController::class, 'reject'])->name('reject');
        Route::get('/{disposal}/dokumen/download', [DisposalAsetController::class, 'downloadDokumen'])->name('dokumen.download');
    });

    // ========================================================
    // MODUL INDUK: CUSTOMIZED
    // ========================================================
    Route::prefix('konfigurasi')->name('konfigurasi.')->group(function () {
        // Submodul A: Field Mutasi Aset (dipertahankan apa adanya)
        Route::prefix('field-aset')->name('field-aset.')->group(function () {
            Route::get('/', [CustomFieldController::class, 'index'])->name('index');
            Route::post('/', [CustomFieldController::class, 'store'])->name('store');
            Route::post('/reorder', [CustomFieldController::class, 'reorder'])->name('reorder');
            Route::put('/{customField}', [CustomFieldController::class, 'update'])->name('update');
            Route::patch('/{customField}/toggle', [CustomFieldController::class, 'toggle'])->name('toggle');
            Route::delete('/{customField}', [CustomFieldController::class, 'destroy'])->name('destroy');

            // Master Lokasi & Personel (Mutasi Aset)
            Route::post('/lokasi', [CustomFieldController::class, 'storeLokasi'])->name('lokasi.store');
            Route::put('/lokasi/{lokasi}', [CustomFieldController::class, 'updateLokasi'])->name('lokasi.update');
            Route::patch('/lokasi/{lokasi}/toggle', [CustomFieldController::class, 'toggleLokasi'])->name('lokasi.toggle');
            Route::delete('/lokasi/{lokasi}', [CustomFieldController::class, 'destroyLokasi'])->name('lokasi.destroy');

            Route::post('/personel', [CustomFieldController::class, 'storePersonel'])->name('personel.store');
            Route::put('/personel/{personel}', [CustomFieldController::class, 'updatePersonel'])->name('personel.update');
            Route::patch('/personel/{personel}/toggle', [CustomFieldController::class, 'togglePersonel'])->name('personel.toggle');
            Route::delete('/personel/{personel}', [CustomFieldController::class, 'destroyPersonel'])->name('personel.destroy');
        });

        // Submodul B: Field Sistem Tiket (Kategori + Field Form & Tabel)
        Route::prefix('tiket')->name('tiket.')->group(function () {
            Route::get('/', [TicketConfigController::class, 'index'])->name('index');

            // Kategori Tiket Actions
            Route::post('/categories', [TicketConfigController::class, 'storeCategory'])->name('categories.store');
            Route::put('/categories/{category}', [TicketConfigController::class, 'updateCategory'])->name('categories.update');
            Route::delete('/categories/{category}', [TicketConfigController::class, 'destroyCategory'])->name('categories.destroy');
            Route::patch('/categories/{category}/toggle', [TicketConfigController::class, 'toggleCategory'])->name('categories.toggle');

            // Field Tiket Actions
            Route::post('/fields', [TicketConfigController::class, 'storeField'])->name('fields.store');
            Route::put('/fields/{field}', [TicketConfigController::class, 'updateField'])->name('fields.update');
            Route::delete('/fields/{field}', [TicketConfigController::class, 'destroyField'])->name('fields.destroy');
            Route::post('/fields/reorder', [TicketConfigController::class, 'reorderFields'])->name('fields.reorder');
            Route::patch('/fields/{field}/toggle', [TicketConfigController::class, 'toggleField'])->name('fields.toggle');
            Route::patch('/fields/{field}/toggle-list', [TicketConfigController::class, 'toggleFieldList'])->name('fields.toggle-list');
            Route::patch('/fields/{field}/toggle-form', [TicketConfigController::class, 'toggleFieldForm'])->name('fields.toggle-form');
        });
    });

    // Backward-Compatibility Aliases & Redirects (URL lama tetap jalan tanpa merusak link manapun)
    Route::get('/admin/custom-fields', function (\Illuminate\Http\Request $request) {
        return redirect()->route('konfigurasi.field-aset.index', $request->query());
    })->name('admin.custom-fields.index');
    Route::post('/admin/custom-fields', [CustomFieldController::class, 'store'])->name('admin.custom-fields.store');
    Route::post('/admin/custom-fields/reorder', [CustomFieldController::class, 'reorder'])->name('admin.custom-fields.reorder');
    Route::put('/admin/custom-fields/{customField}', [CustomFieldController::class, 'update'])->name('admin.custom-fields.update');
    Route::patch('/admin/custom-fields/{customField}/toggle', [CustomFieldController::class, 'toggle'])->name('admin.custom-fields.toggle');
    Route::delete('/admin/custom-fields/{customField}', [CustomFieldController::class, 'destroy'])->name('admin.custom-fields.destroy');

    Route::get('/kategori-tiket', function (\Illuminate\Http\Request $request) {
        return redirect()->route('konfigurasi.tiket.index', array_merge(['tab' => 'kategori'], $request->query()));
    })->name('ticket-categories.index');
    Route::post('/kategori-tiket', [TicketConfigController::class, 'storeCategory'])->name('ticket-categories.store');
    Route::put('/kategori-tiket/{category}', [TicketConfigController::class, 'updateCategory'])->name('ticket-categories.update');
    Route::delete('/kategori-tiket/{category}', [TicketConfigController::class, 'destroyCategory'])->name('ticket-categories.destroy');
    Route::patch('/kategori-tiket/{category}/toggle', [TicketConfigController::class, 'toggleCategory'])->name('ticket-categories.toggle');

    Route::prefix('admin')->name('admin.')->middleware('superadmin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::post('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
    });
    Route::post('/admin/audit-log/rehash', [App\Http\Controllers\Admin\AuditLogController::class, 'rehash'])
    ->name('admin.audit-log.rehash');
});
