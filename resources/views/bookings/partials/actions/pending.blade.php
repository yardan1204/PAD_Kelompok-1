{{-- Status pending -> panel Verify untuk Kepala Lab / Admin Akademik --}}
<form method="POST" action="{{ route('bookings.approve', $booking) }}">
    @csrf
    <button type="submit"
            class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
        ✓ Approve / Verify
    </button>
</form>
<p class="mt-1 text-[11px] text-slate-500">Kepala Lab / Admin Akademik</p>
