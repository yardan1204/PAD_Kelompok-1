{{-- Link navigasi. Hanya tampil jika route-nya sudah didaftarkan di routes/web.php,
     sehingga layout tidak error ketika fitur anggota lain belum di-merge. --}}
@props(['route', 'active'])

@if (Route::has($route))
    <a href="{{ route($route) }}"
       @class([
           'rounded-md px-3 py-1.5 font-medium transition',
           'bg-teal-50 text-teal-800' => request()->routeIs($active),
           'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! request()->routeIs($active),
       ])>
        {{ $slot }}
    </a>
@endif
