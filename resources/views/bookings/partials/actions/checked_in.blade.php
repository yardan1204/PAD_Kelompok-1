{{-- Status checked_in -> simulasi scan QR check-out (event real-time) --}}
<form method="POST" action="{{ route('bookings.check-out', $booking) }}">
    @csrf
    <button type="submit"
            class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg bg-slate-700 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-1">
        <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M7 12h10"/></svg>
        Simulasi Scan QR Check-Out
    </button>
</form>
