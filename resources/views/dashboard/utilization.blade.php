@extends('layouts.app')

@section('title', 'Dashboard Utilisasi Ruang & Lab')

@push('head')
    {{-- Refresh berkala otomatis untuk live update status ruang --}}
    <meta http-equiv="refresh" content="30">
@endpush

@section('content')
    {{-- Header Halaman --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Dashboard Utilisasi Ruang &amp; Laboratorium</h1>
            <p class="mt-1 text-sm text-slate-500">
                Pemantauan efisiensi pemanfaatan ruang, perbandingan jadwal vs aktual, serta deteksi <em>ghost booking</em>.
            </p>
        </div>
        <div class="flex items-center gap-2 text-xs font-medium text-slate-600 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-xs">
            <span class="relative flex h-2.5 w-2.5">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
            </span>
            <span>Live Monitoring</span>
            <span class="text-slate-300">|</span>
            <span class="text-slate-500">Tersedia {{ $period['available_hours'] }} jam/ruang</span>
        </div>
    </div>

    {{-- Filter Rentang Periode Tanggal --}}
    <section class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-xs">
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <div>
                    <label for="start_date" class="block text-xs font-medium uppercase tracking-wider text-slate-500">Dari Tanggal</label>
                    <input type="date" id="start_date" name="start" value="{{ request('start', $period['start']) }}"
                           class="mt-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                </div>
                <div>
                    <label for="end_date" class="block text-xs font-medium uppercase tracking-wider text-slate-500">Sampai Tanggal</label>
                    <input type="date" id="end_date" name="end" value="{{ request('end', $period['end']) }}"
                           class="mt-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                </div>
                <div class="flex items-end gap-2 pt-4 sm:pt-0">
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                        Terapkan Filter
                    </button>
                    @if (request()->has('start') || request()->has('end'))
                        <a href="{{ route('dashboard') }}"
                           class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            <div class="text-xs text-slate-500 sm:text-right">
                <span class="font-medium text-slate-700">Periode Terpilih:</span><br>
                {{ \Carbon\Carbon::parse($period['start'])->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($period['end'])->translatedFormat('d M Y') }}
            </div>
        </form>

        @if ($errors->any())
            <div class="mt-3 rounded-lg bg-rose-50 p-2.5 text-xs text-rose-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
    </section>

    @php
        $roomList = collect($rooms);
        $totalRooms = $summary['total_rooms'] ?? $roomList->count();
        $avgUtilRate = $roomList->count() > 0 ? round($roomList->avg('utilization_rate'), 1) : 0;
        $totalGhosts = $roomList->sum('ghost_bookings');
    @endphp

    {{-- Kartu KPI & Ringkasan --}}
    <section class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Card 1: Status Live --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status Live Saat Ini</p>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-semibold tabular-nums text-slate-900">{{ $totalRooms }}</span>
                <span class="text-sm text-slate-500">total ruang</span>
            </div>
            <div class="mt-3 flex flex-wrap gap-1.5 text-xs">
                <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 font-medium text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    {{ $summary['available_now'] }} Tersedia
                </span>
                <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 font-medium text-amber-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    {{ $summary['booked_now'] }} Dipesan
                </span>
                <span class="inline-flex items-center gap-1 rounded-md bg-sky-50 px-2 py-0.5 font-medium text-sky-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                    {{ $summary['occupied_now'] }} Terpakai
                </span>
            </div>
        </div>

        {{-- Card 2: Rata-rata Utilisasi --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rata-rata Utilisasi</p>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-semibold tabular-nums text-slate-900">{{ $avgUtilRate }}<span class="text-xl text-slate-400">%</span></span>
                <span class="text-xs text-slate-500">seluruh ruangan</span>
            </div>
            <div class="mt-4">
                <x-progress-bar :value="$avgUtilRate" color="bg-teal-600" />
            </div>
        </div>

        {{-- Card 3: Utilisasi Kategori (Under / Over) --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Distribusi Pemanfaatan</p>
            <div class="mt-3 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="flex items-center gap-1.5 text-slate-600">
                        <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                        Under-utilized (&lt;30%)
                    </span>
                    <span class="font-semibold tabular-nums text-amber-700">{{ $summary['underutilized'] }} ruang</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="flex items-center gap-1.5 text-slate-600">
                        <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                        Over-utilized (&ge;80%)
                    </span>
                    <span class="font-semibold tabular-nums text-rose-700">{{ $summary['overutilized'] }} ruang</span>
                </div>
            </div>
        </div>

        {{-- Card 4: Ghost Bookings --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ghost Booking Terdeteksi</p>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-semibold tabular-nums text-rose-600">{{ $totalGhosts }}</span>
                <span class="text-xs text-slate-500">sesi no-show</span>
            </div>
            <p class="mt-3 text-xs text-slate-500">
                Peminjaman disetujui yang tidak di-check-in melebihi toleransi waktu.
            </p>
        </div>
    </section>

    {{-- Alert Rekomendasi Alokasi (Jika ada underutilized / overutilized) --}}
    @if (!empty($underutilized) || !empty($overutilized))
        <div class="mb-6 grid gap-4 sm:grid-cols-2">
            @if (!empty($underutilized))
                <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4">
                    <div class="flex items-center gap-2">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-200 text-xs font-bold text-amber-800">!</span>
                        <h2 class="text-sm font-semibold text-amber-900">Perhatian: Ruangan Under-utilized</h2>
                    </div>
                    <p class="mt-1 text-xs text-amber-800">
                        Ruang berikut di bawah target efisiensi (&lt;30%). Pertimbangkan penjadwalan ulang atau alokasi kegiatan tambahan:
                    </p>
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach ($underutilized as $code)
                            <span class="rounded bg-white px-2 py-0.5 font-mono text-xs font-semibold text-amber-800 shadow-2xs border border-amber-200">
                                {{ $code }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (!empty($overutilized))
                <div class="rounded-xl border border-rose-200 bg-rose-50/70 p-4">
                    <div class="flex items-center gap-2">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-200 text-xs font-bold text-rose-800">!</span>
                        <h2 class="text-sm font-semibold text-rose-900">Peringatan: Ruangan Over-utilized</h2>
                    </div>
                    <p class="mt-1 text-xs text-rose-800">
                        Ruang berikut mendekati / melebihi kapasitas (&ge;80%). Pertimbangkan pengalihan ke ruang alternatif atau penambahan jadwal:
                    </p>
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach ($overutilized as $code)
                            <span class="rounded bg-white px-2 py-0.5 font-mono text-xs font-semibold text-rose-800 shadow-2xs border border-rose-200">
                                {{ $code }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Tabel Rincian Utilisasi per Ruangan --}}
    <section>
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-sm font-semibold text-slate-900">Rincian Efisiensi &amp; Utilisasi Ruang</h2>
            <div class="flex items-center gap-3 text-xs text-slate-500">
                <span class="flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                    Terjadwal
                </span>
                <span class="flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-teal-600"></span>
                    Aktual (Utilisasi)
                </span>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-xs">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="px-4 py-3">Ruangan &amp; Tipe</th>
                        <th scope="col" class="px-4 py-3">Status Live</th>
                        <th scope="col" class="px-4 py-3">Terjadwal</th>
                        <th scope="col" class="px-4 py-3">Aktual &amp; Utilisasi</th>
                        <th scope="col" class="px-4 py-3 text-right">Ghost Booking</th>
                        <th scope="col" class="px-4 py-3">Kategori</th>
                        <th scope="col" class="px-4 py-3">Metrik Khusus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 align-top">
                    @forelse ($rooms as $room)
                        <tr class="hover:bg-slate-50">
                            {{-- Ruangan & Tipe --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-semibold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">
                                        {{ $room['room_code'] }}
                                    </span>
                                    <span class="font-medium text-slate-900">{{ $room['room_name'] }}</span>
                                </div>
                                <div class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                                    <span class="capitalize font-medium text-slate-600">{{ $room['room_type'] }}</span>
                                    @if (!empty($room['academic_unit']))
                                        <span>&bull;</span>
                                        <span>{{ $room['academic_unit'] }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Status Live --}}
                            <td class="whitespace-nowrap px-4 py-3">
                                <x-status-badge :status="$room['live_status']" />
                            </td>

                            {{-- Terjadwal --}}
                            <td class="w-44 px-4 py-3">
                                <div class="flex items-baseline justify-between text-xs">
                                    <span class="font-semibold tabular-nums text-slate-800">{{ $room['scheduled_rate'] }}%</span>
                                    <span class="text-slate-500 tabular-nums">{{ $room['scheduled_hours'] }} jam</span>
                                </div>
                                <div class="mt-1.5">
                                    <x-progress-bar :value="$room['scheduled_rate']" color="bg-indigo-500" />
                                </div>
                            </td>

                            {{-- Aktual & Utilisasi --}}
                            <td class="w-44 px-4 py-3">
                                <div class="flex items-baseline justify-between text-xs">
                                    <span class="font-semibold tabular-nums text-slate-800">{{ $room['utilization_rate'] }}%</span>
                                    <span class="text-slate-500 tabular-nums">{{ $room['actual_hours'] }} jam</span>
                                </div>
                                <div class="mt-1.5">
                                    <x-progress-bar :value="$room['utilization_rate']" color="bg-teal-600" />
                                </div>
                            </td>

                            {{-- Ghost Booking --}}
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <span class="font-semibold tabular-nums {{ $room['ghost_bookings'] > 0 ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                    {{ $room['ghost_bookings'] }}
                                </span>
                                <span class="text-xs text-slate-400">/ {{ $room['approved_bookings'] }}</span>
                                <div class="text-[11px] tabular-nums text-slate-500">
                                    {{ $room['ghost_rate'] }}% no-show
                                </div>
                            </td>

                            {{-- Kategori --}}
                            <td class="whitespace-nowrap px-4 py-3">
                                <x-status-badge :status="$room['category']" />
                            </td>

                            {{-- Metrik Khusus (Strategy) --}}
                            <td class="px-4 py-3 text-xs text-slate-600">
                                @if (!empty($room['metrics']))
                                    <ul class="space-y-1">
                                        @foreach ($room['metrics'] as $key => $val)
                                            <li class="flex items-center justify-between gap-2 border-b border-slate-100 pb-0.5 last:border-0 last:pb-0">
                                                <span class="text-slate-500 capitalize">{{ str_replace('_', ' ', $key) }}:</span>
                                                <span class="font-medium text-slate-800 tabular-nums">
                                                    @if (is_numeric($val))
                                                        {{ str_contains($key, 'ratio') || str_contains($key, 'rate') ? $val . '%' : (str_contains($key, 'hours') ? $val . ' jam' : $val) }}
                                                    @else
                                                        {{ $val ?? '-' }}
                                                    @endif
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                                Belum ada data ruangan atau aktivitas pada periode ini.
                                <a href="{{ route('rooms.create') }}" class="font-medium text-teal-700 hover:underline">Tambah ruangan baru</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
