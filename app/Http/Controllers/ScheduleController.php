<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduleRequest;
use App\Models\Schedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller untuk mengelola jadwal resmi perkuliahan dan praktikum.
 * Jadwal ini menjadi dasar perhitungan scheduled utilization pada ruangan.
 */
class ScheduleController extends Controller
{
    /**
     * Menampilkan daftar jadwal perkuliahan/praktikum terurut berdasarkan waktu mulai.
     */
    public function index(): View
    {
        $schedules = Schedule::with('room')
            ->orderBy('start_time')
            ->paginate(10);

        return view('schedules.index', [
            'schedules' => $schedules,
        ]);
    }

    /**
     * Menyimpan jadwal baru dengan status default 'scheduled'.
     */
    public function store(
        StoreScheduleRequest $request
    ): RedirectResponse {
        Schedule::create([
            ...$request->validated(),
            'status' => 'scheduled',
        ]);

        return redirect()
            ->route('schedules.index')
            ->with(
                'success',
                'Jadwal berhasil ditambahkan.'
            );
    }
}
