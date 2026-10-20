{{-- FE-03 · Jadwal Ruang (dasar perhitungan Scheduled Utilization)
     Variabel dari ScheduleController@index:
       $schedules  paginator Schedule (dengan relasi room)
       $rooms      (opsional) collection Room untuk form tambah jadwal.
                   Jika Controller belum mengirim $rooms, form tidak ditampilkan. --}}
@extends('layouts.app')

@section('title', 'Jadwal Ruang')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Jadwal Ruang</h1>
        <p class="mt-1 text-sm text-slate-500">Jadwal resmi perkuliahan/praktikum yang menjadi acuan <em>scheduled utilization</em>.</p>
    </div>

    <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
        {{-- Daftar jadwal --}}
        <div class="min-w-0 flex-1">
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-4 py-3">Kegiatan</th>
                            <th scope="col" class="px-4 py-3">Ruang</th>
                            <th scope="col" class="px-4 py-3">Waktu</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($schedules as $schedule)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-900">{{ $schedule->title }}</td>
                                <td class="px-4 py-3">
                                    <p class="text-slate-900">{{ $schedule->room?->name }}</p>
                                    <p class="font-mono text-xs text-slate-500">{{ $schedule->room?->code }}</p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <p class="text-slate-900">{{ $schedule->start_time->format('d M Y') }}</p>
                                    <p class="text-xs tabular-nums text-slate-500">{{ $schedule->start_time->format('H:i') }} – {{ $schedule->end_time->format('H:i') }}</p>
                                </td>
                                <td class="px-4 py-3"><x-status-badge :status="$schedule->status" /></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-slate-500">Belum ada jadwal.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $schedules->links() }}
            </div>
        </div>

        {{-- Form tambah jadwal --}}
        @isset($rooms)
            <form method="POST" action="{{ route('schedules.store') }}" class="w-full shrink-0 space-y-4 rounded-xl lg:w-80 border border-slate-200 bg-white p-5">
                @csrf
                <h2 class="text-sm font-semibold text-slate-900">Tambah Jadwal</h2>

                <div>
                    <label for="room_id" class="block text-sm font-medium text-slate-700">Ruang <span class="text-rose-600">*</span></label>
                    <select id="room_id" name="room_id" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                        <option value="">— Pilih ruang —</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>{{ $room->code }} — {{ $room->name }}</option>
                        @endforeach
                    </select>
                    @error('room_id')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-slate-700">Nama Kegiatan <span class="text-rose-600">*</span></label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}" required maxlength="150" placeholder="Praktikum ALPRO"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                    @error('title')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="start_time" class="block text-sm font-medium text-slate-700">Mulai <span class="text-rose-600">*</span></label>
                    <input id="start_time" name="start_time" type="datetime-local" value="{{ old('start_time') }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                    @error('start_time')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_time" class="block text-sm font-medium text-slate-700">Selesai <span class="text-rose-600">*</span></label>
                    <input id="end_time" name="end_time" type="datetime-local" value="{{ old('end_time') }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                    @error('end_time')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                    Simpan Jadwal
                </button>
            </form>
        @endisset
    </div>
@endsection
