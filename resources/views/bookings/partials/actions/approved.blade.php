{{-- Status approved -> simulasi scan QR check-in (event real-time) --}}
<form method="POST" action="{{ route('bookings.checkin', $booking) }}">
    @csrf
    @method('PATCH')
    <button type="submit"
            class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg bg-sky-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1">
        <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M7 12h10"/></svg>
        Simulasi Scan QR Check-In
    </button>
</form>
