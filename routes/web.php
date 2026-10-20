<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;

Route::get('/', function () {
    return view('welcome');
});

// ROOM

Route::get('/rooms', [
    RoomController::class,
    'index',
])->name('rooms.index');

Route::get('/rooms/create', [
    RoomController::class,
    'create',
])->name('rooms.create');

Route::post('/rooms', [
    RoomController::class,
    'store',
])->name('rooms.store');

// SCHEDULE

Route::get('/schedules', [
    ScheduleController::class,
    'index',
])->name('schedules.index');

Route::post('/schedules', [
    ScheduleController::class,
    'store',
])->name('schedules.store');
