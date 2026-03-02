<?php

use App\Http\Controllers\KategoriMenuController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES — tanpa auth
| Diakses pelanggan via scan QR, tidak perlu login
|--------------------------------------------------------------------------
*/

// Route root mengarah ke login (kecuali /order untuk pelanggan)
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/order', [OrderController::class, 'index'])->name('order.index');
Route::post('/order', [OrderController::class, 'store'])->name('order.store');

/*
|--------------------------------------------------------------------------
| AUTH ROUTES — bawaan Breeze
| Jangan dihapus, ini untuk login/logout/register
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| INTERNAL ROUTES — wajib login
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // -------------------------------------------------------
    // DASHBOARD
    // -------------------------------------------------------
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    // -------------------------------------------------------
    // KATEGORI MENU
    // -------------------------------------------------------
    Route::get('/kategori', [KategoriMenuController::class, 'index'])
        ->middleware('permission:view_kategori')
        ->name('kategori.index');

    Route::post('/kategori', [KategoriMenuController::class, 'store'])
        ->middleware('permission:create_kategori')
        ->name('kategori.store');

    Route::put('/kategori/{id}', [KategoriMenuController::class, 'update'])
        ->middleware('permission:edit_kategori')
        ->name('kategori.update');

    Route::delete('/kategori/{id}', [KategoriMenuController::class, 'destroy'])
        ->middleware('permission:delete_kategori')
        ->name('kategori.destroy');

    // -------------------------------------------------------
    // MENU
    // -------------------------------------------------------
    Route::get('/menu', [MenuController::class, 'index'])
        ->middleware('permission:view_menu')
        ->name('menu.index');

    Route::get('/menu/create', [MenuController::class, 'create'])
        ->middleware('permission:create_menu')
        ->name('menu.create');

    Route::post('/menu', [MenuController::class, 'store'])
        ->middleware('permission:create_menu')
        ->name('menu.store');

    Route::get('/menu/{id}', [MenuController::class, 'show'])
        ->middleware('permission:view_menu')
        ->name('menu.show');

    Route::get('/menu/{id}/edit', [MenuController::class, 'edit'])
        ->middleware('permission:edit_menu')
        ->name('menu.edit');

    Route::put('/menu/{id}', [MenuController::class, 'update'])
        ->middleware('permission:edit_menu')
        ->name('menu.update');

    Route::delete('/menu/{id}', [MenuController::class, 'destroy'])
        ->middleware('permission:delete_menu')
        ->name('menu.destroy');

    // -------------------------------------------------------
    // STOK
    // -------------------------------------------------------
    Route::get('/stok', [StokController::class, 'index'])
        ->middleware('permission:view_stok')
        ->name('stok.index');

    Route::post('/stok/{id}/tambah', [StokController::class, 'tambah'])
        ->middleware('permission:manage_stok')
        ->name('stok.tambah');

    Route::post('/stok/{id}/set', [StokController::class, 'set'])
        ->middleware('permission:manage_stok')
        ->name('stok.set');

    // -------------------------------------------------------
    // PESANAN
    // PENTING: route /pesanan/histori harus SEBELUM /pesanan/{id}
    // agar Laravel tidak salah baca 'histori' sebagai {id}
    // -------------------------------------------------------
    Route::get('/pesanan/histori', [PesananController::class, 'histori'])
        ->middleware('permission:view_histori_pesanan')
        ->name('pesanan.histori');

    Route::get('/pesanan', [PesananController::class, 'index'])
        ->middleware('permission:view_pesanan')
        ->name('pesanan.index');

    Route::get('/pesanan/{id}', [PesananController::class, 'show'])
        ->middleware('permission:view_pesanan')
        ->name('pesanan.show');

    Route::patch('/pesanan/{id}/status', [PesananController::class, 'updateStatus'])
        ->middleware('permission:proses_pesanan')
        ->name('pesanan.status');

    Route::post('/pesanan/{id}/bayar', [PesananController::class, 'konfirmasiBayar'])
        ->middleware('permission:konfirmasi_pembayaran')
        ->name('pesanan.bayar');

    Route::delete('/pesanan/{id}', [PesananController::class, 'destroy'])
        ->middleware('permission:delete_pesanan')
        ->name('pesanan.destroy');

        // -------------------------------------------------------
    // USERS
    // -------------------------------------------------------
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:view_users')
        ->name('users.index');

    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:create_users')
        ->name('users.store');

    Route::get('/users/{id}', [UserController::class, 'show'])
        ->middleware('permission:view_users')
        ->name('users.show');

    Route::put('/users/{id}', [UserController::class, 'update'])
        ->middleware('permission:edit_users')
        ->name('users.update');

    Route::delete('/users/{id}', [UserController::class, 'destroy'])
        ->middleware('permission:delete_users')
        ->name('users.destroy');

    // -------------------------------------------------------
    // ROLES
    // -------------------------------------------------------
    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:view_roles')
        ->name('roles.index');

    Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])
        ->middleware('permission:edit_roles')
        ->name('roles.edit');

    Route::put('/roles/{id}', [RoleController::class, 'update'])
        ->middleware('permission:edit_roles')
        ->name('roles.update');

    Route::post('/roles/{id}/reset', [RoleController::class, 'reset'])
        ->middleware('permission:edit_roles')
        ->name('roles.reset');

    // -------------------------------------------------------
    // LAPORAN
    // -------------------------------------------------------
    Route::get('/laporan', [LaporanController::class, 'index'])
        ->middleware('permission:view_laporan')
        ->name('laporan.index');

    Route::get('/laporan/export-excel', [LaporanController::class, 'exportExcel'])
        ->middleware('permission:view_laporan')
        ->name('laporan.export-excel');

    Route::get('/laporan/export-pdf', [LaporanController::class, 'exportPdf'])
        ->middleware('permission:view_laporan')
        ->name('laporan.export-pdf');

    // -------------------------------------------------------
    // ROLES
    // -------------------------------------------------------
    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:view_roles')
        ->name('roles.index');

    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('permission:create_roles')
        ->name('roles.store');

    Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])
        ->middleware('permission:edit_roles')
        ->name('roles.edit');

    Route::put('/roles/{id}', [RoleController::class, 'update'])
        ->middleware('permission:edit_roles')
        ->name('roles.update');

    Route::post('/roles/{id}/reset', [RoleController::class, 'reset'])
        ->middleware('permission:edit_roles')
        ->name('roles.reset');

    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])
        ->middleware('permission:delete_roles')
        ->name('roles.destroy');

});