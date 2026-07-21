<?php

use App\Http\Controllers\DrugController;
use App\Http\Controllers\Orders\OrderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Fallback dashboard — only reached if a logged-in user has none of the
// 5 portal roles assigned. Keeps things safe rather than erroring out.
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// One protected route per portal. Spatie's `role:` middleware blocks
// anyone whose account doesn't have that exact role — including if they
// type the URL directly or reuse a link, regardless of what ?role= was
// on the login page.
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('dashboard-admin');
    })->name('dashboard.admin');
});

Route::middleware(['auth', 'verified', 'role:pharmacist'])->group(function () {
    Route::get('/pharmacist/dashboard', function () {
        return view('dashboard-pharmacist');
    })->name('dashboard.pharmacist');
});

Route::middleware(['auth', 'verified', 'role:pharmacy_manager'])->group(function () {
    Route::get('/pharmacy-manager/dashboard', function () {
        return view('dashboard-pharmacy-manager');
    })->name('dashboard.pharmacy_manager');
});

Route::middleware(['auth', 'verified', 'role:procurement_officer'])->group(function () {
    Route::get('/procurement-officer/dashboard', function () {
        return view('dashboard-procurement-officer');
    })->name('dashboard.procurement_officer');
});

Route::middleware(['auth', 'verified', 'role:store_manager'])->group(function () {
    Route::get('/store-manager/dashboard', function () {
        return view('dashboard-store-manager');
    })->name('dashboard.store_manager');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Drug Inventory — scoped under each role's dashboard namespace.
// Role middleware ensures users cannot access another portal's drug routes.
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin/dashboard')->name('admin.dashboard.')->group(function () {
    Route::resource('drugs', DrugController::class);
});

Route::middleware(['auth', 'verified', 'role:procurement_officer'])->prefix('procurement/dashboard')->name('procurement.dashboard.')->group(function () {
    Route::resource('drugs', DrugController::class);
});

Route::middleware(['auth', 'verified', 'role:store_manager'])->prefix('store-manager/dashboard')->name('store-manager.dashboard.')->group(function () {
    Route::resource('drugs', DrugController::class);
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

Route::middleware(['auth', 'verified', 'role:pharmacy_manager'])->prefix('pharmacy-manager/dashboard')->name('pharmacy-manager.dashboard.')->group(function () {
    Route::resource('drugs', DrugController::class);
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

Route::middleware(['auth', 'verified', 'role:pharmacist'])->prefix('pharmacist/dashboard')->name('pharmacist.dashboard.')->group(function () {
    Route::resource('drugs', DrugController::class);
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// Procurement Orders — full workflow for procurement officer & admin.
Route::middleware(['auth', 'verified', 'role:procurement_officer|admin'])->prefix('procurement/dashboard')->name('procurement.dashboard.')->group(function () {
    Route::post('orders/{order}/approve', [OrderController::class, 'approve'])->name('orders.approve')->middleware('role:admin');
    Route::post('orders/{order}/receive', [OrderController::class, 'receive'])->name('orders.receive')->middleware('role:admin');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::resource('orders', OrderController::class);
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
});

require __DIR__.'/auth.php';
