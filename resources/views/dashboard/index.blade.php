{{-- FE-01 · Live Utilization Dashboard
     View ini HANYA menampilkan data yang sudah dihitung oleh Controller/Service.
     Variabel yang diharapkan (lihat KONTRAK-VIEW.md):
       $liveStatus        array  ['AVAILABLE' => int, 'BOOKED' => int, 'OCCUPIED' => int]
       $kpi               array  scheduled_utilization, actual_utilization, effective_score,
                                 ghost_booking_count, category
       $roomUtilizations  iterable of array per ruang
       $lastUpdated       string waktu data terakhir dihitung --}}
@extends('layouts.app')

@section('title', 'Live Utilization Dashboard')

@push('head')
    {{-- Halaman memuat ulang otomatis supaya status berubah tanpa rekap manual --}}
    <meta http-equiv="refresh" content="30">
@endpush

@section('content')
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Live Utilization Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">
                Ruang atau laboratorium mana yang belum dimanfaatkan secara efektif, dan kapan kapasitas tambahan benar-benar diperlukan?
            </p>
        </div>
        <p class="flex items-center gap-2 text-xs text-slate-500">
            <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
            </span>
            Live · diperbarui {{ $lastUpdated }}
        </p>
    </div>

    {{-- ============ KPI: LIVE ROOM STATUS ============ --}}
    <section aria-labelledby="live-status-title" class="mb-6">
        <h2 id="live-status-title" class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Live Room Status</h2>
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ($liveStatus as $status => $count)
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <x-status-badge :status="$status" />
                    <p class="mt-3 text-3xl font-semibold tabular-nums text-slate-900">{{ $count }}</p>
                    <p class="text-sm text-slate-500">ruang</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============ KPI: UTILISASI ============ --}}
    <section aria-labelledby="kpi-title" class="mb-8">
        <h2 id="kpi-title" class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Ringkasan Utilisasi</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-500">Scheduled Utilization</p>
                <p class="mt-2 text-3xl font-semibold tabular-nums text-slate-900">{{ $kpi['scheduled_utilization'] }}<span class="text-lg text-slate-400">%</span></p>
                <div class="mt-3"><x-progress-bar :value="$kpi['scheduled_utilization']" color="bg-indigo-500" /></div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-500">Actual Utilization</p>
                <p class="mt-2 text-3xl font-semibold tabular-nums text-slate-900">{{ $kpi['actual_utilization'] }}<span class="text-lg text-slate-400">%</span></p>
                <div class="mt-3"><x-progress-bar :value="$kpi['actual_utilization']" /></div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-500">Effective Score</p>
                <p class="mt-2 text-3xl font-semibold tabular-nums text-slate-900">{{ $kpi['effective_score'] }}</p>
                <div class="mt-3 flex items-center gap-2 text-xs text-slate-500">
                    Kategori <x-status-badge :status="$kpi['category']" />
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-500">Ghost Booking</p>
                <p class="mt-2 text-3xl font-semibold tabular-nums text-rose-600">{{ $kpi['ghost_booking_count'] }}</p>
                <p class="mt-3 text-xs text-slate-500">booking disetujui tanpa check-in</p>
            </div>
        </div>
    </section>

    {{-- ============ UTILISASI PER RUANG ============ --}}
    <section aria-labelledby="per-room-title">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h2 id="per-room-title" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Scheduled vs Actual per Ruang</h2>
            <div class="flex flex-wrap gap-2 text-xs text-slate-500">
                <x-status-badge status="UNDERUTILIZED" />
                <x-status-badge status="OPTIMAL" />
                <x-status-badge status="OVERUTILIZED" />
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th scope="col" class="px-4 py-3">Ruang</th>
                        <th scope="col" class="px-4 py-3">Status Live</th>
                        <th scope="col" class="px-4 py-3">Scheduled</th>
                        <th scope="col" class="px-4 py-3">Actual</th>
                        <th scope="col" class="px-4 py-3 text-right">Effective Score</th>
                        <th scope="col" class="px-4 py-3 text-right">Ghost Booking</th>
                        <th scope="col" class="px-4 py-3">Kategori</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($roomUtilizations as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-900">{{ $row['room_name'] }}</p>
                                <p class="text-xs text-slate-500">{{ $row['room_code'] }}</p>
                            </td>
                            <td class="px-4 py-3"><x-status-badge :status="$row['live_status']" /></td>
                            <td class="w-40 px-4 py-3">
                                <p class="mb-1 tabular-nums">{{ $row['scheduled_utilization'] }}%</p>
                                <x-progress-bar :value="$row['scheduled_utilization']" color="bg-indigo-500" />
                            </td>
                            <td class="w-40 px-4 py-3">
                                <p class="mb-1 tabular-nums">{{ $row['actual_utilization'] }}%</p>
                                <x-progress-bar :value="$row['actual_utilization']" />
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $row['effective_score'] }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $row['ghost_booking_count'] }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$row['category']" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                                Belum ada data utilisasi. Tambahkan ruang, jadwal, dan booking terlebih dahulu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
