<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Models\AcademicUnit;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller untuk manajemen data master ruangan dan laboratorium.
 * Bertanggung jawab menampilkan daftar, form tambah, dan persistensi data ruangan.
 */
class RoomController extends Controller
{
    /**
     * Menampilkan daftar master ruangan dan laboratorium beserta relasi
     * unit akademik dan fasilitas terkait menggunakan paginasi.
     */
    public function index(): View
    {
        $rooms = Room::with(['academicUnit', 'facilities'])
            ->orderBy('name')
            ->paginate(10);

        return view('rooms.index', [
            'rooms' => $rooms,
        ]);
    }

    /**
     * Menampilkan form untuk mendaftarkan ruangan baru beserta
     * pilihan unit akademik (fakultas/jurusan).
     */
    public function create(): View
    {
        $academicUnits = AcademicUnit::orderBy('name')
            ->get();

        return view('rooms.create', [
            'academicUnits' => $academicUnits,
        ]);
    }

    /**
     * Menyimpan data ruangan baru ke basis data setelah divalidasi oleh StoreRoomRequest.
     */
    public function store(
        StoreRoomRequest $request
    ): RedirectResponse {
        Room::create($request->validated());

        return redirect()
            ->route('rooms.index')
            ->with(
                'success',
                'Ruangan berhasil ditambahkan.'
            );
    }
}
