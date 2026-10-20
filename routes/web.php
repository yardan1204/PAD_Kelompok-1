<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UtilizationDashboardController;

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

// BOOKING

Route::get('/bookings', [
    BookingController::class,
    'index',
])->name('bookings.index');

Route::get('/bookings/create', [
    BookingController::class,
    'create',
])->name('bookings.create');

Route::post('/bookings', [
    BookingController::class,
    'store',
])->name('bookings.store');

Route::match(['post', 'patch'], '/bookings/{booking}/approve', [
    BookingController::class,
    'approve',
])->name('bookings.approve');

Route::post('/bookings/{booking}/reject', [
    BookingController::class,
    'reject',
])->name('bookings.reject');

Route::match(['post', 'patch'], '/bookings/{booking}/check-in', [
    BookingController::class,
    'checkIn',
])->name('bookings.check-in');
Route::match(['post', 'patch'], '/bookings/{booking}/check-in', [
    BookingController::class,
    'checkIn',
])->name('bookings.checkin');

Route::match(['post', 'patch'], '/bookings/{booking}/check-out', [
    BookingController::class,
    'checkOut',
])->name('bookings.check-out');
Route::match(['post', 'patch'], '/bookings/{booking}/check-out', [
    BookingController::class,
    'checkOut',
])->name('bookings.checkout');

// DASHBOARD UTILISASI

Route::get('/dashboard/utilization', [
    UtilizationDashboardController::class,
    'index',
])->name('dashboard.utilization');

Route::get('/dashboard', [
    UtilizationDashboardController::class,
    'index',
])->name('dashboard');
