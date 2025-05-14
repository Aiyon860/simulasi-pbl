<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TokoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\ReturBarangController;
use App\Http\Controllers\KategoriBarangController;
use App\Http\Controllers\PenerimaanBarangController;
use App\Http\Controllers\PengirimanBarangController;
use App\Http\Controllers\DetailReturBarangController;
use App\Http\Controllers\PenerimaanDiCabangController;
use App\Http\Controllers\DetailPenerimaanBarangController;
use App\Http\Controllers\StatusPengirimanBarangController;
use App\Models\CabangKePusat;
use App\Http\Controllers\CabangKePusatController;
use App\Http\Controllers\SupplierKePusatController;
use App\Http\Controllers\PenerimaanDiPusatController;
use App\Http\Controllers\DetailGudangController;
use App\Http\Controllers\CabangKeTokoController;
use App\Http\Controllers\GudangDanTokoController;

Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect('/login'));
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Routes untuk User & Role Management - hanya SuperAdmin
    Route::middleware(['role:SuperAdmin, Supervisor'])->group(function () {
        Route::resource('roles', RoleController::class);
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/activate', [UserController::class, 'activate'])
            ->name('users.activate');
        Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate'])
            ->name('users.deactivate');
    });

    // Routes untuk Categories - SuperAdmin, Supervisor, Admin
    Route::middleware(['role:SuperAdmin,Supervisor,Admin'])->group(function () {
        Route::resource('categories', KategoriBarangController::class);
        // Activate/Deactivate hanya untuk SuperAdmin dan Supervisor
        Route::middleware(['role:SuperAdmin,Supervisor'])->group(function () {
            Route::patch('categories/{category}/activate', [KategoriBarangController::class, 'activate'])
                ->name('categories.activate');
            Route::patch('categories/{category}/deactivate', [KategoriBarangController::class, 'deactivate'])
                ->name('categories.deactivate');
        });
    });

    // Routes untuk Barang - SuperAdmin, Supervisor, Admin, Supplier, Buyer
    Route::middleware(['role:SuperAdmin,Supervisor,Admin,Supplier,Buyer'])->group(function () {
        Route::resource('barangs', BarangController::class);
        // Activate/Deactivate hanya untuk SuperAdmin saja
        Route::middleware(['role:SuperAdmin'])->group(function () {
            Route::patch('barangs/{barang}/activate', [BarangController::class, 'activate'])
                ->name('barangs.activate');
            Route::patch('barangs/{barang}/deactivate', [BarangController::class, 'deactivate'])
                ->name('barangs.deactivate');
        });
    });

//     // Routes untuk Gudang - SuperAdmin, Supervisor, Admin
//     Route::middleware(['role:SuperAdmin,Supervisor,Admin'])->group(function () {
//         Route::resource('gudangs', GudangController::class);
//         // Activate/Deactivate hanya untuk SuperAdmin dan Supervisor
//         Route::middleware(['role:SuperAdmin,Supervisor'])->group(function () {
//             Route::patch('gudangs/{gudang}/activate', [GudangController::class, 'activate'])
//                 ->name('gudangs.activate');
//             Route::patch('gudangs/{gudang}/deactivate', [GudangController::class, 'deactivate'])
//                 ->name('gudangs.deactivate');
//         });
//     });

//     // Routes untuk Toko - SuperAdmin, Supervisor, Admin
//     Route::middleware(['role:SuperAdmin,Supervisor,Admin'])->group(function () {
//         Route::resource('tokos', TokoController::class);
//         // Activate/Deactivate hanya untuk SuperAdmin dan Supervisor
//         Route::middleware(['role:SuperAdmin,Supervisor'])->group(function () {
//             Route::patch('tokos/{toko}/activate', [TokoController::class, 'activate'])
//                 ->name('tokos.activate');
//             Route::patch('tokos/{toko}/deactivate', [TokoController::class, 'deactivate'])
//                 ->name('tokos.deactivate');
//         });
//     });

//     // Routes untuk Penerimaan Barang - Admin, Staff
//     Route::middleware(['role:Admin,Supervisor,Staff'])->group(function () {
//     // Routes untuk Penerimaan Barang - SuperAdmin, Admin
//     Route::middleware(['role:SuperAdmin,Supervisor,Admin'])->group(function () {
//         Route::resource('penerimaan-barang', PenerimaanBarangController::class);

//         Route::get('/penerimaan-barang/{penerimaanBarang}details/{detailPenerimaan}', [PenerimaanBarangController::class, 'showDetail'])
//             ->name('penerimaan-barang.show-detail');
//     });

//     // Status Pengiriman Barang - hanya Admin
//     Route::middleware(['role:Admin'])->group(function () {
//         Route::resource('status-pengiriman-barang', StatusPengirimanBarangController::class);
//     });

//     // Routes untuk Pengiriman Barang - Admin, Staff, Supplier
//     Route::middleware(['role:Admin,Supervisor,Staff'])->group(function () {
//     // Status Pengiriman Barang - hanya SuperAdmin
//     Route::middleware(['role:SuperAdmin'])->group(function () {
//         Route::resource('status-pengiriman-barang', StatusPengirimanBarangController::class);
//     });

//     // Routes untuk Pengiriman Barang - SuperAdmin, Admin, Supplier
//     Route::middleware(['role:SuperAdmin,Supervisor,Admin'])->group(function () {
//         // Buat resource pengiriman, tapi restrict mana yang bisa diakses oleh Supplier
//         Route::resource('pengiriman-barang', PengirimanBarangController::class);

//         // Detail view untuk semua role yang bisa akses pengiriman
//         Route::get('pengiriman-barang/{pengirimanBarang}/details/{detailPengirimanBarang}', [PengirimanBarangController::class, 'showDetail'])
//             ->name('pengiriman-barang.detail.show');
//     });

//     // Routes untuk Retur Barang - Admin, Staff, Buyer
//     Route::middleware(['role:Admin,Supervisor,Staff,Buyer'])->group(function () {
//     // Routes untuk Retur Barang - SuperAdmin, Admin, Buyer
//     Route::middleware(['role:SuperAdmin,Supervisor,Admin,Buyer'])->group(function () {
//         Route::resource('retur-barang', ReturBarangController::class);
//         // Detail view untuk semua role yang bisa akses retur
//         Route::get('detail-retur-barang/{detailReturBarang}', [DetailReturBarangController::class, 'show'])
//             ->name('detail-retur-barang.show');
//         Route::get('retur-barang/{returBarang}/detail/create', [DetailReturBarangController::class, 'create'])
//             ->name('detail-retur-barang.create');
//         Route::post('detail-retur-barang', [DetailReturBarangController::class, 'store'])
//             ->name('detail-retur-barang.store');

//         // Operasi edit dan delete hanya untuk Admin dan Staff
//         Route::middleware(['role:Admin,Supervisor,Staff'])->group(function () {
//         // Operasi edit dan delete hanya untuk SuperAdmin dan Admin
//         Route::middleware(['role:SuperAdmin,Supervisor,Admin'])->group(function () {
//             Route::get('detail-retur-barang/{detailReturBarang}/edit', [DetailReturBarangController::class, 'edit'])
//                 ->name('detail-retur-barang.edit');
//             Route::put('detail-retur-barang/{detailReturBarang}', [DetailReturBarangController::class, 'update'])
//                 ->name('detail-retur-barang.update');
//             Route::delete('detail-retur-barang/{detailReturBarang}', [DetailReturBarangController::class, 'destroy'])
//                 ->name('detail-retur-barang.destroy');
//         });
//     });

//     Route::middleware(['role:buyer'])->group(function () {
//         Route::get('/orders', [PengirimanBarangController::class, 'ordersIndex'])
//             ->name('orders.index');
//         Route::get('/orders/{pengirimanBarang}', [PengirimanBarangController::class, 'ordersShow'])
//             ->name('orders.show');

//         Route::get('/orders/{pengirimanBarang}/details/{detailPengirimanBarang}', [PengirimanBarangController::class, 'ordersDetailShow'])
//             ->name('orders.detail.show');
//     });
// });

});
