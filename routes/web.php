<?php

use App\Http\Controllers\ApiDocsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('gedung', BuildingController::class)
        ->parameters(['gedung' => 'building'])
        ->names('buildings')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'can:manage-module')
        ->middlewareFor(['destroy'], 'can:delete-module');

    Route::resource('ruang', RoomController::class)
        ->parameters(['ruang' => 'room'])
        ->names('rooms')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'can:manage-module')
        ->middlewareFor(['destroy'], 'can:delete-module');

    Route::resource('fasilitas', FacilityController::class)
        ->parameters(['fasilitas' => 'facility'])
        ->names('facilities')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'can:manage-module')
        ->middlewareFor(['destroy'], 'can:delete-module');

    Route::resource('jadwal', ScheduleController::class)
        ->parameters(['jadwal' => 'schedule'])
        ->names('schedules')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'can:manage-module')
        ->middlewareFor(['destroy'], 'can:delete-module');

    Route::patch('jadwal/{schedule}/status', [ScheduleController::class, 'updateStatus'])
        ->middleware('can:manage-module')
        ->name('schedules.status');

    Route::get('laporan', [ReportController::class, 'index'])->name('reports.index');
    Route::get('laporan/cetak', [ReportController::class, 'print'])->name('reports.print');
    Route::get('laporan/export/ruang', [ReportController::class, 'exportRooms'])->name('reports.rooms.csv');
    Route::get('laporan/export/jadwal', [ReportController::class, 'exportSchedules'])->name('reports.schedules.csv');

    Route::get('profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profil', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('profil/token', [ProfileController::class, 'regenerateToken'])->name('profile.token');

    Route::get('dokumentasi-api', [ApiDocsController::class, 'index'])->name('api-docs');
});
