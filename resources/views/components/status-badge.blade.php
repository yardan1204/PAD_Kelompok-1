{{-- Badge status. Warna dipilih murni oleh CSS lewat atribut data-status,
     jadi tidak ada if/else atau pemetaan status di dalam Blade.
     Contoh: <x-status-badge :status="$booking->status" /> --}}
@props(['status'])

<span data-status="{{ $status }}"
      {{ $attributes->class([
          'inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide whitespace-nowrap',
          'bg-slate-100 text-slate-700',
          // hijau: tersedia / optimal / normal
          'data-[status=available]:bg-emerald-50 data-[status=available]:text-emerald-700',
          'data-[status=AVAILABLE]:bg-emerald-50 data-[status=AVAILABLE]:text-emerald-700',
          'data-[status=OPTIMAL]:bg-emerald-50 data-[status=OPTIMAL]:text-emerald-700',
          'data-[status=normal]:bg-emerald-50 data-[status=normal]:text-emerald-700',
          'data-[status=NORMAL]:bg-emerald-50 data-[status=NORMAL]:text-emerald-700',
          // kuning: menunggu / dipesan / kurang dimanfaatkan
          'data-[status=pending]:bg-amber-50 data-[status=pending]:text-amber-700',
          'data-[status=BOOKED]:bg-amber-50 data-[status=BOOKED]:text-amber-700',
          'data-[status=booked]:bg-amber-50 data-[status=booked]:text-amber-700',
          'data-[status=UNDERUTILIZED]:bg-amber-50 data-[status=UNDERUTILIZED]:text-amber-700',
          'data-[status=underutilized]:bg-amber-50 data-[status=underutilized]:text-amber-700',
          // biru: sedang dipakai
          'data-[status=occupied]:bg-sky-50 data-[status=occupied]:text-sky-700',
          'data-[status=OCCUPIED]:bg-sky-50 data-[status=OCCUPIED]:text-sky-700',
          'data-[status=checked_in]:bg-sky-50 data-[status=checked_in]:text-sky-700',
          // ungu: disetujui / terjadwal
          'data-[status=approved]:bg-indigo-50 data-[status=approved]:text-indigo-700',
          'data-[status=scheduled]:bg-indigo-50 data-[status=scheduled]:text-indigo-700',
          // merah: rusak / ditolak / kelebihan beban
          'data-[status=maintenance]:bg-rose-50 data-[status=maintenance]:text-rose-700',
          'data-[status=damaged]:bg-rose-50 data-[status=damaged]:text-rose-700',
          'data-[status=rejected]:bg-rose-50 data-[status=rejected]:text-rose-700',
          'data-[status=OVERUTILIZED]:bg-rose-50 data-[status=OVERUTILIZED]:text-rose-700',
          'data-[status=overutilized]:bg-rose-50 data-[status=overutilized]:text-rose-700',
      ]) }}>
    {{ $status }}
</span>
