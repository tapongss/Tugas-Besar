<?php

use App\Http\Controllers\Api\BuildingController;
use App\Http\Controllers\Api\FacilityController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.json')->prefix('v1')->group(function () {
    Route::get('/', fn () => response()->json([
        'name' => 'Ruang & Fasilitas Sidang API',
        'module' => 'Sistem Tugas Akhir & Skripsi — Individu D',
        'version' => '1.0.0',
        'documentation' => url('/dokumentasi-api'),
        'authentication' => 'Bearer token pada header Authorization untuk endpoint tulis (POST/PUT/DELETE).',
        'resources' => [
            'buildings' => url('/api/v1/buildings'),
            'rooms' => url('/api/v1/rooms'),
            'facilities' => url('/api/v1/facilities'),
            'schedules' => url('/api/v1/schedules'),
        ],
    ]));

    Route::get('buildings', [BuildingController::class, 'index'])->name('api.buildings.index');
    Route::get('buildings/{building}', [BuildingController::class, 'show'])->name('api.buildings.show');

    Route::get('rooms', [RoomController::class, 'index'])->name('api.rooms.index');
    Route::get('rooms/{room}', [RoomController::class, 'show'])->name('api.rooms.show');

    Route::get('facilities', [FacilityController::class, 'index'])->name('api.facilities.index');
    Route::get('facilities/{facility}', [FacilityController::class, 'show'])->name('api.facilities.show');

    Route::get('schedules', [ScheduleController::class, 'index'])->name('api.schedules.index');
    Route::get('schedules/{schedule}', [ScheduleController::class, 'show'])->name('api.schedules.show');

    Route::middleware(['api.token', 'api.can-manage'])->group(function () {
        Route::post('buildings', [BuildingController::class, 'store'])->name('api.buildings.store');
        Route::put('buildings/{building}', [BuildingController::class, 'update'])->name('api.buildings.update');

        Route::post('rooms', [RoomController::class, 'store'])->name('api.rooms.store');
        Route::put('rooms/{room}', [RoomController::class, 'update'])->name('api.rooms.update');

        Route::post('facilities', [FacilityController::class, 'store'])->name('api.facilities.store');
        Route::put('facilities/{facility}', [FacilityController::class, 'update'])->name('api.facilities.update');

        Route::post('schedules', [ScheduleController::class, 'store'])->name('api.schedules.store');
        Route::put('schedules/{schedule}', [ScheduleController::class, 'update'])->name('api.schedules.update');
    });

    Route::middleware(['api.token', 'api.can-delete'])->group(function () {
        Route::delete('buildings/{building}', [BuildingController::class, 'destroy'])->name('api.buildings.destroy');
        Route::delete('rooms/{room}', [RoomController::class, 'destroy'])->name('api.rooms.destroy');
        Route::delete('facilities/{facility}', [FacilityController::class, 'destroy'])->name('api.facilities.destroy');
        Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('api.schedules.destroy');
    });
});
