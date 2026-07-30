<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscrepancyReportController;
use App\Http\Controllers\DispensingRecordController;
use App\Http\Controllers\DrugController;
use App\Http\Controllers\HospitalOrderController;
use App\Http\Controllers\HospitalShipmentController;
use App\Http\Controllers\Orders\OrderController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegionalReportController;
use App\Http\Controllers\StockStatusController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect(getRoleDashboardRoute());
    }

    return view('welcome');
})->name('home');

// Fallback dashboard — only reached if a logged-in user has none of the
// 5 portal roles assigned. Keeps things safe rather than erroring out.
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// One protected route per portal. Spatie's `role:` middleware blocks
// anyone whose account doesn't have that exact role — including if they
// type the URL directly or reuse a link, regardless of what ?role= was
// on the login page.
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('dashboard.admin');
});

Route::middleware(['auth', 'verified', 'role:pharmacist'])->group(function () {
    Route::get('/pharmacist/dashboard', [DashboardController::class, 'index'])->name('dashboard.pharmacist');
});

Route::middleware(['auth', 'verified', 'role:pharmacy_manager'])->group(function () {
    Route::get('/pharmacy-manager/dashboard', [DashboardController::class, 'index'])->name('dashboard.pharmacy_manager');
});

Route::middleware(['auth', 'verified', 'role:procurement_officer'])->group(function () {
    Route::get('/procurement-officer/dashboard', [DashboardController::class, 'index'])->name('dashboard.procurement_officer');
});

Route::middleware(['auth', 'verified', 'role:store_manager'])->group(function () {
    Route::get('/store-manager/dashboard', [DashboardController::class, 'index'])->name('dashboard.store_manager');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/currency/rates', [\App\Http\Controllers\CurrencyController::class, 'rates'])->name('currency.rates');
    Route::get('/currency/convert', [\App\Http\Controllers\CurrencyController::class, 'convert'])->name('currency.convert');
});

// Drug Inventory & Procurement Orders — scoped under each role's portal prefix.
// Dashboard home stays at /{role}/dashboard; modules live at /{role}/drugs and /{role}/orders.
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.dashboard.')->group(function () {
    Route::post('medicines/{medicine}/deactivate', [MedicineController::class, 'deactivate'])->name('medicines.deactivate');
    Route::post('medicines/{medicine}/activate', [MedicineController::class, 'activate'])->name('medicines.activate');
    Route::resource('medicines', MedicineController::class);
    Route::resource('drugs', DrugController::class);
    Route::post('orders/{order}/approve', [OrderController::class, 'approve'])->name('orders.approve');
    Route::post('orders/{order}/advance-pipeline', [OrderController::class, 'advancePipeline'])->name('orders.advance-pipeline');
    Route::post('orders/{order}/receive', [OrderController::class, 'receive'])->name('orders.receive');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::resource('orders', OrderController::class);
    Route::resource('transfers', StockTransferController::class)->only(['index', 'show']);
    Route::resource('users', UserController::class)->except(['show']);
    Route::get('reports/stock-status', [StockStatusController::class, 'index'])->name('reports.stock-status.index');
});

Route::middleware(['auth', 'verified', 'role:procurement_officer'])->prefix('procurement-officer')->name('procurement-officer.dashboard.')->group(function () {
    Route::post('medicines/{medicine}/deactivate', [MedicineController::class, 'deactivate'])->name('medicines.deactivate');
    Route::post('medicines/{medicine}/activate', [MedicineController::class, 'activate'])->name('medicines.activate');
    Route::resource('medicines', MedicineController::class)->except(['destroy']);
    Route::resource('drugs', DrugController::class);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('orders/{order}/advance-pipeline', [OrderController::class, 'advancePipeline'])->name('orders.advance-pipeline');
    Route::resource('orders', OrderController::class);
    Route::resource('transfers', StockTransferController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('reports/stock-status', [StockStatusController::class, 'index'])->name('reports.stock-status.index');
});

Route::middleware(['auth', 'verified', 'role:store_manager'])->prefix('store-manager')->name('store-manager.dashboard.')->group(function () {
    Route::resource('drugs', DrugController::class)->only(['index', 'show']);
    Route::post('transfers/{transfer}/receive', [StockTransferController::class, 'receive'])->name('transfers.receive');
    Route::resource('transfers', StockTransferController::class)->only(['index', 'show']);
    Route::resource('hospital-orders', HospitalOrderController::class)->only(['index', 'show']);
    Route::post('hospital-orders/{hospitalOrder}/approve', [HospitalOrderController::class, 'approve'])->name('hospital-orders.approve');
    Route::post('hospital-orders/{hospitalOrder}/reject', [HospitalOrderController::class, 'reject'])->name('hospital-orders.reject');
    Route::post('hospital-orders/{hospitalOrder}/ship', [HospitalOrderController::class, 'ship'])->name('hospital-orders.ship');
    Route::resource('hospital-shipments', HospitalShipmentController::class)->only(['index', 'show'])->parameters(['hospital-shipments' => 'transfer']);
    Route::resource('discrepancies', DiscrepancyReportController::class)->only(['index', 'show']);
    Route::post('discrepancies/{discrepancy}/resolve', [DiscrepancyReportController::class, 'resolve'])->name('discrepancies.resolve');
    Route::get('reports/regional', [RegionalReportController::class, 'index'])->name('reports.regional.index');
    Route::get('reports/stock-status', [StockStatusController::class, 'index'])->name('reports.stock-status.index');
});

Route::middleware(['auth', 'verified', 'role:pharmacy_manager'])->prefix('pharmacy-manager')->name('pharmacy-manager.dashboard.')->group(function () {
    Route::resource('drugs', DrugController::class);
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('hospital-orders', HospitalOrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('hospital-orders/{hospitalOrder}/receive', [HospitalOrderController::class, 'receive'])->name('hospital-orders.receive');
    Route::resource('hospital-shipments', HospitalShipmentController::class)->only(['index', 'show'])->parameters(['hospital-shipments' => 'transfer']);
    Route::resource('discrepancies', DiscrepancyReportController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('patients', PatientController::class)->except(['destroy']);
    Route::resource('dispensing', DispensingRecordController::class)->only(['index', 'show'])->parameters(['dispensing' => 'dispensing']);
    Route::get('reports/stock-status', [StockStatusController::class, 'index'])->name('reports.stock-status.index');
});

Route::middleware(['auth', 'verified', 'role:pharmacist'])->prefix('pharmacist')->name('pharmacist.dashboard.')->group(function () {
    Route::resource('drugs', DrugController::class);
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::resource('patients', PatientController::class)->except(['destroy']);
    Route::resource('dispensing', DispensingRecordController::class)->only(['index', 'create', 'store', 'show'])->parameters(['dispensing' => 'dispensing']);
    Route::get('reports/stock-status', [StockStatusController::class, 'index'])->name('reports.stock-status.index');
});

Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// Shared drug API routes (Receiving/Dispensing modules) + legacy /drugs redirects.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('drugs/search/{batch_number}', [DrugController::class, 'searchByBatch'])->name('drugs.search');
    Route::get('drugs/batch/{batch_number}/details', [DrugController::class, 'getBatchDetails'])->name('drugs.batch-details');

    // Legacy URLs — redirect to the user's role-specific drug inventory.
    Route::get('/drugs/create', function () {
        return redirect(getDashboardDrugRoute('create'));
    });

    Route::get('/drugs/{drug}/edit', function (int $drug) {
        return redirect(getDashboardDrugRoute('edit', $drug));
    })->whereNumber('drug');

    Route::get('/drugs/{drug}', function (int $drug) {
        return redirect(getDashboardDrugRoute('show', $drug));
    })->whereNumber('drug');

    Route::get('/drugs', function () {
        return redirect(getDashboardDrugRoute('index'));
    });

    // Legacy URLs — redirect old /{role}/dashboard/* module paths to /{role}/*
    Route::redirect('/admin/dashboard/drugs', '/admin/drugs');
    Route::redirect('/admin/dashboard/orders', '/admin/orders');
    Route::redirect('/procurement-officer/dashboard/drugs', '/procurement-officer/drugs');
    Route::redirect('/procurement-officer/dashboard/orders', '/procurement-officer/orders');
    Route::redirect('/procurement/dashboard/drugs', '/procurement-officer/drugs');
    Route::redirect('/procurement/dashboard/orders', '/procurement-officer/orders');
    Route::redirect('/store-manager/dashboard/drugs', '/store-manager/drugs');
    Route::redirect('/store-manager/dashboard/orders', '/store-manager/orders');
    Route::redirect('/pharmacy-manager/dashboard/drugs', '/pharmacy-manager/drugs');
    Route::redirect('/pharmacy-manager/dashboard/orders', '/pharmacy-manager/orders');
    Route::redirect('/pharmacist/dashboard/drugs', '/pharmacist/drugs');
    Route::redirect('/pharmacist/dashboard/orders', '/pharmacist/orders');
});

require __DIR__.'/auth.php';
