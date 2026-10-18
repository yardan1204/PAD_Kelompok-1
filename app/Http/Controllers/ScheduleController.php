<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduleRequest;
use App\Models\Schedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $schedules = Schedule::with('room')
            ->orderBy('start_time')
            ->paginate(10);

        return view('schedules.index', [
            'schedules' => $schedules,
        ]);
    }

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
