@props([
    'type' => 'card', // card, room, stat, table, text
    'count' => 1,
    'rows' => 4,
    'lines' => 3,
])

{{-- Komponen Skeleton Loading untuk memberi affordance kepada pengguna
     bahwa konten sedang dimuat pada koneksi lambat.
     Memenuhi kriteria aksesibilitas: role="status", aria-busy="true", aria-live="polite". --}}
<div role="status" aria-busy="true" aria-live="polite" {{ $attributes->merge(['class' => 'cs-skeleton-wrapper space-y-4']) }}>
    <span class="sr-only">Memuat konten...</span>

    @if ($type === 'card' || $type === 'room')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @for ($i = 0; $i < $count; $i++)
                <div class="cs-skeleton-card space-y-4">
                    {{-- Thumbnail placeholder --}}
                    <div class="cs-skeleton h-48 w-full rounded-lg"></div>
                    
                    {{-- Header title & badge --}}
                    <div class="flex items-center justify-between gap-2">
                        <div class="cs-skeleton cs-skeleton-title mb-0"></div>
                        <div class="cs-skeleton cs-skeleton-badge"></div>
                    </div>
                    
                    {{-- Description lines --}}
                    <div class="space-y-2">
                        <div class="cs-skeleton cs-skeleton-text"></div>
                        <div class="cs-skeleton cs-skeleton-text w-4/5"></div>
                    </div>
                    
                    {{-- Footer actions --}}
                    <div class="flex items-center justify-between pt-2 border-t border-[color:var(--cs-color-border)]">
                        <div class="cs-skeleton cs-skeleton-btn"></div>
                        <div class="cs-skeleton h-4 w-20"></div>
                    </div>
                </div>
            @endfor
        </div>
    @elseif ($type === 'stat')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @for ($i = 0; $i < $count; $i++)
                <div class="cs-card cs-card-body space-y-3">
                    <div class="cs-skeleton h-3.5 w-24"></div>
                    <div class="cs-skeleton h-9 w-20"></div>
                    <div class="cs-skeleton h-3 w-36"></div>
                </div>
            @endfor
        </div>
    @elseif ($type === 'table')
        <div class="cs-card overflow-hidden">
            <div class="p-4 border-b border-[color:var(--cs-color-border)] bg-[color:var(--cs-color-surface-muted)] flex gap-4">
                <div class="cs-skeleton h-4 w-1/4"></div>
                <div class="cs-skeleton h-4 w-1/4"></div>
                <div class="cs-skeleton h-4 w-1/4"></div>
                <div class="cs-skeleton h-4 w-1/4"></div>
            </div>
            <div class="divide-y divide-[color:var(--cs-color-border)]">
                @for ($r = 0; $r < $rows; $r++)
                    <div class="p-4 flex gap-4 items-center">
                        <div class="cs-skeleton h-4 w-1/4"></div>
                        <div class="cs-skeleton h-4 w-1/3"></div>
                        <div class="cs-skeleton h-4 w-1/6"></div>
                        <div class="cs-skeleton h-8 w-24 rounded-md"></div>
                    </div>
                @endfor
            </div>
        </div>
    @else
        <div class="space-y-2.5">
            @for ($l = 0; $l < $lines; $l++)
                <div class="cs-skeleton cs-skeleton-text {{ $l === 0 ? 'cs-skeleton-title' : ($l === $lines - 1 ? 'w-1/2' : 'w-full') }}"></div>
            @endfor
        </div>
    @endif
</div>
