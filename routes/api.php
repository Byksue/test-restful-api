<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/register', [\App\Http\Controllers\UserController::class, 'register']);
    Route::post('/logout', [\App\Http\Controllers\UserController::class, 'logout']);

    // Absensi
    Route::middleware('role:supervisor|employee')->group(function(){
        Route::post('/absen-masuk', [\App\Http\Controllers\AbsensiController::class, 'absenMasuk']);
        Route::post('/absen-keluar', [\App\Http\Controllers\AbsensiController::class, 'absenKeluar']);
        Route::get('/rekap-absensi/{pegawai_id}', [\App\Http\Controllers\AbsensiController::class, 'rekapAbsensiPegawai']);
    });

    Route::middleware('role:supervisor')->group(function(){
        Route::post('/approve-absensi', [\App\Http\Controllers\AbsensiController::class, 'approveAbsensi']);
    });

    Route::get('/user', function(){
        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil didapatkan',
            'data' => [
                'user' => Auth::user(),
                'roles' => Auth::user()->getRoleNames()[0],
            ]
        ]);
    });
});


Route::post('/login', [\App\Http\Controllers\UserController::class, 'login'])->middleware('guest')->name('login');
