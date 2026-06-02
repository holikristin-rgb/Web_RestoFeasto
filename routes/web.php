<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KasirController;

/*
|--------------------------------------------------------------------------
| Web Routes - RestoFeasto
|--------------------------------------------------------------------------
*/

// --- 1. Halaman Publik ---
Route::get('/', [CustomerController::class, 'index'])->name('index');

// --- 2. Form Login & Registrasi ---
Route::get('/login', function () {
    if (Auth::check()) {
        return redirect()->route('beranda');
    }
    return view('auth.login', ['role' => 'Pelanggan', 'default_email' => '']);
})->name('login');

Route::get('/login/admin', function () {
    if (Auth::check() && Auth::user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return view('auth.login', ['role' => 'Admin', 'default_email' => 'admin@restofeasto.com']);
})->name('login.admin');

Route::get('/login/kasir', function () {
    if (Auth::check() && Auth::user()->role === 'kasir') {
        return redirect()->route('kasir.dashboard');
    }
    return view('auth.login', ['role' => 'Kasir', 'default_email' => 'kasir@restofeasto.com']);
})->name('login.kasir');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/register', function () {
    if (Auth::check()) {
        return redirect()->route('beranda');
    }
    return view('auth.register');
})->name('register');

Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// --- OTP Verification ---
Route::get('/verify-otp', [AuthController::class, 'showVerifyOtpForm'])->name('verify.otp.view');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('verify.otp');
Route::post('/verify-otp/resend', [AuthController::class, 'resendOtp'])->name('verify.otp.resend');

// --- Forgot Password ---
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// --- 3. Keranjang Belanja (Cart) AJAX ---
Route::post('/cart/add', [CustomerController::class, 'cartAdd'])->name('cart.add');
Route::post('/cart/update', [CustomerController::class, 'cartUpdate'])->name('cart.update');
Route::post('/cart/remove', [CustomerController::class, 'cartRemove'])->name('cart.remove');

// --- 4. Area Terproteksi (Middleware Auth) ---
Route::middleware(['auth'])->group(function () {

    // Beranda & Pelanggan Utama
    Route::get('/beranda', [CustomerController::class, 'index'])->name('beranda');
    Route::get('/reservasi', [CustomerController::class, 'showReservasi'])->name('reservasi');
    Route::post('/reservasi', [CustomerController::class, 'storeReservasi'])->name('reservasi.store');
    
    // AJAX Table Availability
    Route::get('/tables/available', [CustomerController::class, 'getAvailableTablesAjax'])->name('tables.available');
    
    // Pembayaran, Pembayaran QRIS, & Feedback Ulasan
    Route::get('/pembayaran', [CustomerController::class, 'showPembayaran'])->name('pembayaran');
    Route::post('/pembayaran/{id}', [CustomerController::class, 'storePembayaran'])->name('pembayaran.store');
    Route::post('/pembayaran/{id}/feedback', [CustomerController::class, 'storeFeedback'])->name('feedback.store');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Change Password & Settings
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('change.password');
    Route::post('/admin/settings/qris', [AdminController::class, 'updateQris'])->middleware('role:admin,kasir')->name('admin.settings.qris');

    // --- 5. GRUP ROUTE ADMIN ---
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        
        // Menu CRUD
        Route::post('/menus', [AdminController::class, 'storeMenu'])->name('menu.store');
        Route::put('/menus/{id}', [AdminController::class, 'updateMenu'])->name('menu.update');
        Route::delete('/menus/{id}', [AdminController::class, 'destroyMenu'])->name('menu.destroy');

        // Category CRUD
        Route::post('/categories', [AdminController::class, 'storeCategory'])->name('category.store');
        Route::put('/categories/{id}', [AdminController::class, 'updateCategory'])->name('category.update');
        Route::delete('/categories/{id}', [AdminController::class, 'destroyCategory'])->name('category.destroy');

        // Table CRUD
        Route::post('/tables', [AdminController::class, 'storeMeja'])->name('table.store');
        Route::put('/tables/{id}', [AdminController::class, 'updateMeja'])->name('table.update');
        Route::delete('/tables/{id}', [AdminController::class, 'destroyMeja'])->name('table.destroy');

        // Register Staff
        Route::post('/staff', [AdminController::class, 'storeStaff'])->name('staff.store');
        Route::put('/staff/{id}', [AdminController::class, 'updateStaff'])->name('staff.update');
        Route::delete('/staff/{id}', [AdminController::class, 'destroyStaff'])->name('staff.destroy');
    });

    // --- 6. GRUP ROUTE KASIR ---
    Route::middleware('role:kasir')->prefix('kasir')->name('kasir.')->group(function () {
        Route::get('/dashboard', [KasirController::class, 'index'])->name('dashboard');
        Route::post('/bookings/{id}/confirm', [KasirController::class, 'confirmBooking'])->name('booking.confirm');
        Route::post('/payments/{id}/confirm', [KasirController::class, 'confirmPayment'])->name('payment.confirm');
    });

});

//debug-foto bukti bayar
Route::get('/debug-db', function () {
    return response()->json([
        'database' => \DB::connection()->getDatabaseName(),
        'reservations' => \App\Models\Reservasi::with('pembayaran')->get(),
    ]);
});

Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath)) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*');

Route::get('/storage-file/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath)) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*')->name('storage.file');