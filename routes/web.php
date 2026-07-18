<?php

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

require __DIR__.'/auth.php';