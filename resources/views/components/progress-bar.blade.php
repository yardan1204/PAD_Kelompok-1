{{-- Bar persentase. Nilai sudah dihitung & dibulatkan di Controller/Service;
     lebar bar dibatasi 100% oleh CSS (max-w-full), bukan oleh rumus di view. --}}
@props(['value', 'color' => 'bg-teal-600'])

<div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
    <div class="h-full max-w-full rounded-full {{ $color }}" style="width: {{ $value }}%"></div>
</div>
