{{-- FE-03 · Form Pengajuan Booking (Submit)
     Variabel dari BookingController@create:
       $rooms  collection Room
       $roles  array daftar role stakeholder, contoh ['Mahasiswa', 'Dosen', ...]
     Dikirim ke route bookings.store. Pengecekan bentrok jadwal & kapasitas dilakukan
     di server; pesannya tampil lewat layouts/partials/flash dan @error di bawah. --}}
@extends('layouts.app')

@php
    $roles = $roles ?? ['Mahasiswa', 'Dosen', 'Admin Akademik', 'Kepala Laboratorium', 'Pimpinan Fakultas'];
@endphp

@section('title', 'Ajukan Booking')

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('bookings.index') }}" class="text-sm text-slate-500 hover:text-slate-800">← Kembali ke daftar booking</a>
        <h1 class="mt-2 text-2xl font-semibold text-slate-900">Ajukan Booking Ruang</h1>
        <p class="mt-1 text-sm text-slate-500">
            Pengajuan akan berstatus <x-status-badge status="pending" /> sampai diverifikasi Kepala Laboratorium / Admin Akademik.
        </p>

        <form method="POST" action="{{ route('bookings.store') }}" class="mt-6 space-y-6 rounded-xl border border-slate-200 bg-white p-6">
            @csrf

            <fieldset class="grid gap-5 sm:grid-cols-2">
                <legend class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Data Pemohon</legend>

                <div>
                    <label for="requester_name" class="block text-sm font-medium text-slate-700">Nama Pemohon <span class="text-rose-600">*</span></label>
                    <input id="requester_name" name="requester_name" type="text" value="{{ old('requester_name') }}" required maxlength="150"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                    @error('requester_name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="requester_role" class="block text-sm font-medium text-slate-700">Role Stakeholder <span class="text-rose-600">*</span></label>
                    <select id="requester_role" name="requester_role" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                        <option value="">— Pilih role —</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(old('requester_role') === $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                    @error('requester_role')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </fieldset>

            <hr class="border-slate-100">

            <fieldset class="grid gap-5 sm:grid-cols-2">
                <legend class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Detail Peminjaman</legend>

                <div>
                    <label for="room_id" class="block text-sm font-medium text-slate-700">Ruang / Lab <span class="text-rose-600">*</span></label>
                    <select id="room_id" name="room_id" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                        <option value="">— Pilih ruang —</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>
                                {{ $room->code }} — {{ $room->name }} (kapasitas {{ $room->getCapacity() }})
                            </option>
                        @endforeach
                    </select>
                    @error('room_id')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="participant_count" class="block text-sm font-medium text-slate-700">Jumlah Peserta <span class="text-rose-600">*</span></label>
                    <input id="participant_count" name="participant_count" type="number" value="{{ old('participant_count') }}" required min="1" step="1"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                    @error('participant_count')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="start_time" class="block text-sm font-medium text-slate-700">Waktu Mulai <span class="text-rose-600">*</span></label>
                    <input id="start_time" name="start_time" type="datetime-local" value="{{ old('start_time') }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                    @error('start_time')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_time" class="block text-sm font-medium text-slate-700">Waktu Selesai <span class="text-rose-600">*</span></label>
                    <input id="end_time" name="end_time" type="datetime-local" value="{{ old('end_time') }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                    @error('end_time')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="purpose" class="block text-sm font-medium text-slate-700">Keperluan <span class="text-rose-600">*</span></label>
                    <textarea id="purpose" name="purpose" rows="3" required maxlength="255" placeholder="Contoh: Praktikum Basis Data kelas A"
                              class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </fieldset>

            <p class="rounded-lg bg-slate-50 px-4 py-3 text-xs text-slate-600">
                Sistem akan menolak pengajuan bila jadwal bentrok dengan booking/jadwal lain di ruang yang sama,
                atau jumlah peserta melebihi kapasitas ruang.
            </p>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('bookings.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
                <button type="submit"
                        class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                    Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>
@endsection
