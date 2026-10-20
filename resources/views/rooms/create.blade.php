{{-- FE-02 · Form Tambah Ruang/Lab
     Variabel dari RoomController@create:
       $academicUnits  collection AcademicUnit
     Dikirim ke route rooms.store (validasi server: StoreRoomRequest).
     Atribut required/min/maxlength di bawah hanya validasi dasar di browser. --}}
@extends('layouts.app')

@section('title', 'Tambah Ruang')

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('rooms.index') }}" class="text-sm text-slate-500 hover:text-slate-800">← Kembali ke daftar ruang</a>
        <h1 class="mt-2 text-2xl font-semibold text-slate-900">Tambah Ruang / Laboratorium</h1>
        <p class="mt-1 text-sm text-slate-500">Isi data master ruang. Kolom bertanda <span class="text-rose-600">*</span> wajib diisi.</p>

        <form method="POST" action="{{ route('rooms.store') }}" class="mt-6 space-y-6 rounded-xl border border-slate-200 bg-white p-6">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                {{-- Unit akademik --}}
                <div class="sm:col-span-2">
                    <label for="academic_unit_id" class="block text-sm font-medium text-slate-700">Unit Akademik <span class="text-rose-600">*</span></label>
                    <select id="academic_unit_id" name="academic_unit_id" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                        <option value="">— Pilih unit akademik —</option>
                        @foreach ($academicUnits as $unit)
                            <option value="{{ $unit->id }}" @selected(old('academic_unit_id') == $unit->id)>
                                {{ $unit->code }} — {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('academic_unit_id')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kode --}}
                <div>
                    <label for="code" class="block text-sm font-medium text-slate-700">Kode Ruang <span class="text-rose-600">*</span></label>
                    <input id="code" name="code" type="text" value="{{ old('code') }}" required maxlength="30" placeholder="C301"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                    @error('code')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nama --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Nama Ruang <span class="text-rose-600">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="150" placeholder="Laboratorium C301"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Gedung --}}
                <div>
                    <label for="building" class="block text-sm font-medium text-slate-700">Gedung <span class="text-rose-600">*</span></label>
                    <input id="building" name="building" type="text" value="{{ old('building') }}" required maxlength="100" placeholder="Gedung C"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                    @error('building')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Lantai --}}
                <div>
                    <label for="floor" class="block text-sm font-medium text-slate-700">Lantai</label>
                    <input id="floor" name="floor" type="text" value="{{ old('floor') }}" maxlength="30" placeholder="3"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                    @error('floor')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kapasitas --}}
                <div>
                    <label for="capacity" class="block text-sm font-medium text-slate-700">Kapasitas (orang) <span class="text-rose-600">*</span></label>
                    <input id="capacity" name="capacity" type="number" value="{{ old('capacity') }}" required min="1" step="1" placeholder="30"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                    @error('capacity')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label for="status" class="block text-sm font-medium text-slate-700">Status Awal <span class="text-rose-600">*</span></label>
                    <select id="status" name="status" required
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">
                        <option value="available" @selected(old('status', 'available') === 'available')>Available</option>
                        <option value="occupied" @selected(old('status') === 'occupied')>Occupied</option>
                        <option value="maintenance" @selected(old('status') === 'maintenance')>Maintenance</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-slate-700">Deskripsi</label>
                    <textarea id="description" name="description" rows="3" placeholder="Contoh: Lab komputer, 30 PC, proyektor"
                              class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-1 focus:ring-teal-600">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('rooms.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
                <button type="submit"
                        class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                    Simpan Ruang
                </button>
            </div>
        </form>
    </div>
@endsection
