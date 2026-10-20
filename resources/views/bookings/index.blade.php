{{-- PLACEHOLDER BE (PAD-16): polos agar alur bisa dicoba. Silakan ganti oleh FE. --}}
<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><title>Peminjaman Ruang</title></head>
<body>
<h1>Peminjaman Ruang</h1>
<p><a href="{{ route('bookings.create') }}">+ Ajukan peminjaman</a> |
   <a href="{{ route('dashboard.utilization') }}">Dashboard utilisasi</a></p>

@if (session('success')) <p style="color:green">{{ session('success') }}</p> @endif
@foreach ($errors->all() as $error) <p style="color:red">{{ $error }}</p> @endforeach

<table border="1" cellpadding="4">
    <tr>
        <th>Ruangan</th><th>Peminjam</th><th>Keperluan</th><th>Peserta</th>
        <th>Mulai</th><th>Selesai</th><th>Status</th><th>QR token</th><th>Aksi</th>
    </tr>
    @foreach ($bookings as $booking)
    <tr>
        <td>{{ $booking->room?->name }}</td>
        <td>{{ $booking->user?->name }}</td>
        <td>{{ $booking->purpose }}</td>
        <td>{{ $booking->participant_count }}</td>
        <td>{{ $booking->start_time }}</td>
        <td>{{ $booking->end_time }}</td>
        <td>{{ $booking->status }} @if ($booking->rejection_reason) ({{ $booking->rejection_reason }}) @endif</td>
        <td>{{ $booking->qr_token }}</td>
        <td>
            @if ($booking->canBeDecided())
                <form method="POST" action="{{ route('bookings.approve', $booking) }}">@csrf
                    <button>Setujui</button></form>
                <form method="POST" action="{{ route('bookings.reject', $booking) }}">@csrf
                    <input name="rejection_reason" placeholder="Alasan penolakan" required>
                    <button>Tolak</button></form>
            @endif
            @if ($booking->canCheckIn())
                <form method="POST" action="{{ route('bookings.check-in', $booking) }}">@csrf
                    <input name="qr_token" placeholder="Isi QR (token)" required>
                    <button>Scan Check-In</button></form>
            @endif
            @if ($booking->canCheckOut())
                <form method="POST" action="{{ route('bookings.check-out', $booking) }}">@csrf
                    <input name="qr_token" placeholder="Isi QR (token)" required>
                    <button>Scan Check-Out</button></form>
            @endif
        </td>
    </tr>
    @endforeach
</table>
{{ $bookings->links() }}
</body>
</html>
