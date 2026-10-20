<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveBookingRequest;
use App\Http\Requests\QrScanRequest;
use App\Http\Requests\RejectBookingRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService
    ) {
    }

    public function index(): View
    {
        return view('bookings.index', [
            'bookings' => $this->bookingService->paginateBookings(),
        ]);
    }

    public function create(): View
    {
        return view('bookings.create', [
            'rooms' => $this->bookingService->roomsForBooking(),
        ]);
    }

    public function store(
        StoreBookingRequest $request
    ): RedirectResponse {
        $this->bookingService->submitBooking(
            $request->user(),
            $request->validated()
        );

        return redirect()
            ->route('bookings.index')
            ->with(
                'success',
                'Peminjaman berhasil diajukan dan menunggu persetujuan.'
            );
    }

    public function approve(
        ApproveBookingRequest $request,
        Booking $booking
    ): RedirectResponse {
        $this->bookingService->approve($booking, $request->user());

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Peminjaman disetujui.');
    }

    public function reject(
        RejectBookingRequest $request,
        Booking $booking
    ): RedirectResponse {
        $this->bookingService->reject(
            $booking,
            $request->user(),
            $request->validated('rejection_reason')
        );

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Peminjaman ditolak.');
    }

    public function checkIn(
        QrScanRequest $request,
        Booking $booking
    ): RedirectResponse {
        $this->bookingService->checkIn(
            $booking,
            $request->validated('qr_token')
        );

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Check-in berhasil. Ruangan kini terpakai.');
    }

    public function checkOut(
        QrScanRequest $request,
        Booking $booking
    ): RedirectResponse {
        $this->bookingService->checkOut(
            $booking,
            $request->validated('qr_token')
        );

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Check-out berhasil. Ruangan kembali tersedia.');
    }
}
