{{-- FE-02 · Daftar Ruang/Laboratorium & Fasilitas
     Variabel dari RoomController@index:
       $rooms  paginator Room (dengan relasi academicUnit & facilities)
     Data ditampilkan lewat getter/summary milik Model (getEntitySummary, getCapacity, getStatus). --}}
@extends('layouts.app')

@section('title', 'Ruang & Laboratorium')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Ruang &amp; Laboratorium</h1>
            <p class="mt-1 text-sm text-slate-500">Master data ruang kelas dan laboratorium beserta kapasitas, unit akademik, dan fasilitasnya.</p>
        </div>
        <a href="{{ route('rooms.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
            + Tambah Ruang
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th scope="col" class="px-4 py-3">Kode</th>
                    <th scope="col" class="px-4 py-3">Ruang</th>
                    <th scope="col" class="px-4 py-3">Unit Akademik</th>
                    <th scope="col" class="px-4 py-3">Lokasi</th>
                    <th scope="col" class="px-4 py-3 text-right">Kapasitas</th>
                    <th scope="col" class="px-4 py-3">Status</th>
                    <th scope="col" class="px-4 py-3">Fasilitas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 align-top">
                @forelse ($rooms as $room)
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-600">{{ $room->code }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-900">{{ $room->name }}</p>
                            <p class="text-xs text-slate-500">{{ $room->getEntitySummary() }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $room->academicUnit?->name }}</td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ $room->building }}
                            <span class="block text-xs text-slate-500">Lantai {{ $room->floor ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $room->getCapacity() }} <span class="text-xs text-slate-500">orang</span></td>
                        <td class="px-4 py-3"><x-status-badge :status="$room->getStatus()" /></td>
                        <td class="px-4 py-3">
                            <ul class="flex max-w-xs flex-wrap gap-1.5">
                                @forelse ($room->facilities as $facility)
                                    <li class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs text-slate-700"
                                        title="{{ $facility->getEntitySummary() }}">
                                        {{ $facility->name }}
                                        <span class="tabular-nums text-slate-500">×{{ $facility->getQuantity() }}</span>
                                        <x-status-badge :status="$facility->getStatus()" class="ml-0.5 px-1.5 text-[10px]" />
                                    </li>
                                @empty
                                    <li class="text-xs text-slate-400">Belum ada fasilitas</li>
                                @endforelse
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                            Belum ada ruang terdaftar.
                            <a href="{{ route('rooms.create') }}" class="font-medium text-teal-700 hover:underline">Tambah ruang pertama</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $rooms->links() }}
    </div>
@endsection
