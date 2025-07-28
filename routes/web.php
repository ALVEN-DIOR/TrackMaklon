<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;

Route::get('/', [UserController::class, 'masuk'])
     ->name('login.form');

Route::post('/login', [UserController::class, 'logika_masuk'])->name('login');

// Route debug
Route::get('/debug-admin', function() {
    if (!Auth::check()) {
        return response()->json([
            'status' => 'not_authenticated',
            'message' => 'User not logged in'
        ]);
    }
    
    return response()->json([
        'status' => 'authenticated',
        'user_id' => Auth::id(),
        'user_role' => Auth::user()->role,
        'user_email' => Auth::user()->email,
        'user_no_hp' => Auth::user()->no_hp,
        'is_admin' => Auth::user()->role === 'admin',
        'user_data' => Auth::user()->toArray()
    ]);
})->name('debug.admin');

// Route admin TANPA middleware IsAdmin untuk testing
Route::middleware(['auth'])->group(function () {
    Route::get('/admin', [UserController::class, 'admin'])
         ->name('admin.index');
    Route::post('/progress/update', [UserController::class, 'updateProgress'])
         ->name('progress.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/order-tracker', [UserController::class, 'index'])
         ->name('tracker.index');
    Route::post('/logout', [UserController::class, 'logout'])
         ->name('logout');
});
