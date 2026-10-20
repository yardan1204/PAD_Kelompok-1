<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UtilizationDashboardController;

// ==========================================
// 1. HALAMAN UTAMA (LANDING / HOME)
// ==========================================
// Rute awal '/' langsung diarahkan ke Dashboard Utilisasi
Route::get('/', [
    UtilizationDashboardController::class,
    'index',
])->name('home');

// ==========================================
// 2. MANAJEMEN RUANG & LAB (ROOMS)
// ==========================================

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

// ==========================================
// 3. JADWAL KULIAH & PRAKTIKUM (SCHEDULES)
// ==========================================

Route::get('/schedules', [
    ScheduleController::class,
    'index',
])->name('schedules.index');

Route::post('/schedules', [
    ScheduleController::class,
    'store',
])->name('schedules.store');

// ==========================================
// 4. PEMESANAN RUANGAN (BOOKINGS)
// ==========================================

// Daftar dan form pengajuan
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

// Verifikasi: Approve & Reject oleh Kepala Lab / Admin
Route::match(['post', 'patch'], '/bookings/{booking}/approve', [
    BookingController::class,
    'approve',
])->name('bookings.approve');

Route::post('/bookings/{booking}/reject', [
    BookingController::class,
    'reject',
])->name('bookings.reject');

// Simulasi Kehadiran: Check-In & Check-Out via Scan QR
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

// ==========================================
// 5. LIVE DASHBOARD UTILISASI
// ==========================================

Route::get('/dashboard/utilization', [
    UtilizationDashboardController::class,
    'index',
])->name('dashboard.utilization');

Route::get('/dashboard', [
    UtilizationDashboardController::class,
    'index',
])->name('dashboard');
