{{-- Notifikasi pesan dari Controller:
     - redirect()->with('success', '...')  -> kotak hijau
     - redirect()->with('error', '...')    -> kotak merah
     - validasi gagal / withErrors([...])  -> daftar galat merah --}}

@if (session('success'))
    <div role="status" class="mb-5 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <span aria-hidden="true" class="font-bold">✓</span>
        <p>{{ session('success') }}</p>
    </div>
@endif

@if (session('error'))
    <div role="alert" class="mb-5 flex items-start gap-3 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <span aria-hidden="true" class="font-bold">!</span>
        <p>{{ session('error') }}</p>
    </div>
@endif

@if ($errors->any())
    <div role="alert" class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <p class="font-semibold">Permintaan tidak dapat diproses. Periksa kembali isian berikut:</p>
        <ul class="mt-1 list-disc space-y-0.5 pl-5">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
