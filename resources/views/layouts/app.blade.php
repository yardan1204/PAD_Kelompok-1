<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · Campus Space Utilization</title>

    {{-- Slot tambahan untuk <head>, misalnya auto-refresh di dashboard --}}
    @stack('head')

    {{-- Pakai Vite (Tailwind) bila sudah `npm run dev` / `npm run build`;
         jika belum, fallback ke Tailwind CDN agar halaman tetap tampil rapi. --}}
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4.1.11"></script>
    @endif
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">

    {{-- ===================== NAVIGASI ===================== --}}
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <span class="grid h-8 w-8 place-items-center rounded-md bg-teal-700 text-sm font-bold text-white">CS</span>
                <span class="leading-tight">
                    <span class="block text-sm font-semibold text-slate-900">Campus Space &amp; Lab Utilization</span>
                    <span class="block text-xs text-slate-500">Resource Utilization &amp; Facility Efficiency</span>
                </span>
            </a>

            <nav class="flex flex-wrap gap-1 text-sm">
                <x-nav-link route="dashboard" :active="['dashboard*', 'home']">Dashboard</x-nav-link>
                <x-nav-link route="rooms.index" active="rooms.*">Ruang &amp; Lab</x-nav-link>
                <x-nav-link route="schedules.index" active="schedules.*">Jadwal</x-nav-link>
                <x-nav-link route="bookings.index" active="bookings.*">Booking</x-nav-link>
            </nav>
        </div>
    </header>

    {{-- ===================== KONTEN ===================== --}}
    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6">
        @include('layouts.partials.flash')

        @yield('content')
    </main>

    <footer class="mx-auto max-w-7xl px-4 pb-8 text-xs text-slate-400 sm:px-6">
        PAD Kelompok 1 · Campus Space &amp; Laboratory Utilization System
    </footer>
</body>
</html>
