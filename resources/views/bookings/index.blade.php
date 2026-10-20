{{-- FE-03 · Daftar Booking + Panel Verify + Simulator Scan QR
     Variabel dari BookingController@index:
       $bookings  paginator Booking (dengan relasi room)
     Tombol aksi dipilih berdasarkan nama status lewat partial
     bookings/partials/actions/{status}.blade.php — tanpa if/else di view ini.
     Menambah status baru cukup menambah satu file partial. --}}
@extends('layouts.app')

@section('title', 'Booking Ruang')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Booking Ruang</h1>
            <p class="mt-1 text-sm text-slate-500">Pengajuan, verifikasi, dan simulasi scan QR check-in / check-out.</p>
        </div>
        <a href="{{ route('bookings.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
            + Ajukan Booking
        </a>
    </div>

    {{-- Alur kerja lintas stakeholder (informasi statis) --}}
    <ol class="mb-6 grid gap-2 text-xs sm:grid-cols-4">
        <li class="rounded-lg border border-slate-200 bg-white px-3 py-2">
            <span class="font-semibold text-slate-900">1. Submit</span>
            <span class="block text-slate-500">Mahasiswa / Dosen</span>
        </li>
        <li class="rounded-lg border border-slate-200 bg-white px-3 py-2">
            <span class="font-semibold text-slate-900">2. Verify</span>
            <span class="block text-slate-500">Kepala Lab / Admin Akademik</span>
        </li>
        <li class="rounded-lg border border-slate-200 bg-white px-3 py-2">
            <span class="font-semibold text-slate-900">3. QR Check-In</span>
            <span class="block text-slate-500">Pengguna di depan ruang</span>
        </li>
        <li class="rounded-lg border border-slate-200 bg-white px-3 py-2">
            <span class="font-semibold text-slate-900">4. QR Check-Out</span>
            <span class="block text-slate-500">Pengguna selesai memakai</span>
        </li>
    </ol>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th scope="col" class="px-4 py-3">Pemohon</th>
                    <th scope="col" class="px-4 py-3">Ruang</th>
                    <th scope="col" class="px-4 py-3 text-right">Peserta</th>
                    <th scope="col" class="px-4 py-3">Jadwal</th>
                    <th scope="col" class="px-4 py-3">Keperluan</th>
                    <th scope="col" class="px-4 py-3">Status</th>
                    <th scope="col" class="px-4 py-3">Aktual</th>
                    <th scope="col" class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 align-top">
                @forelse ($bookings as $booking)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-900">{{ $booking->requester_name }}</p>
                            <p class="text-xs text-slate-500">{{ $booking->requester_role }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-slate-900">{{ $booking->room?->name }}</p>
                            <p class="font-mono text-xs text-slate-500">{{ $booking->room?->code }}</p>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $booking->participant_count }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <p class="text-slate-900">{{ $booking->start_time->format('d M Y') }}</p>
                            <p class="text-xs tabular-nums text-slate-500">{{ $booking->start_time->format('H:i') }} – {{ $booking->end_time->format('H:i') }}</p>
                        </td>
                        <td class="max-w-xs px-4 py-3 text-slate-700">{{ $booking->purpose }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$booking->status" /></td>
                        <td class="whitespace-nowrap px-4 py-3 text-xs tabular-nums text-slate-600">
                            <p>In: {{ $booking->checked_in_at?->format('H:i') ?? '—' }}</p>
                            <p>Out: {{ $booking->checked_out_at?->format('H:i') ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @includeFirst(
                                ['bookings.partials.actions.' . $booking->status, 'bookings.partials.actions.none'],
                                ['booking' => $booking]
                            )
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-500">
                            Belum ada pengajuan booking.
                            <a href="{{ route('bookings.create') }}" class="font-medium text-teal-700 hover:underline">Ajukan booking pertama</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $bookings->links() }}
    </div>
@endsection
