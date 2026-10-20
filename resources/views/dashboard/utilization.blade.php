{{-- PLACEHOLDER BE (PAD-17): polos agar data dashboard bisa dilihat. Silakan ganti oleh FE.
     Variabel: $period, $summary, $rooms (array), $underutilized, $overutilized.
     Data yang sama tersedia sebagai JSON (header Accept: application/json). --}}
<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><title>Dashboard Utilisasi</title></head>
<body>
<h1>Dashboard Utilisasi Ruang</h1>
<p>Periode {{ $period['start'] }} s/d {{ $period['end'] }}
   (jam tersedia per ruang: {{ $period['available_hours'] }} jam)</p>

<form method="GET">
    Dari <input type="date" name="start" value="{{ request('start') }}">
    sampai <input type="date" name="end" value="{{ request('end') }}">
    <button>Terapkan</button>
</form>
@foreach ($errors->all() as $error) <p style="color:red">{{ $error }}</p> @endforeach

<p>
    Sekarang: {{ $summary['available_now'] }} available,
    {{ $summary['booked_now'] }} booked,
    {{ $summary['occupied_now'] }} occupied.
    Under-utilized: {{ $summary['underutilized'] }} |
    Over-utilized: {{ $summary['overutilized'] }}
</p>

<table border="1" cellpadding="4">
    <tr>
        <th>Ruang</th><th>Tipe</th><th>Status live</th><th>Terjadwal (jam / %)</th>
        <th>Aktual (jam)</th><th>Utilisasi %</th><th>Ghost</th><th>Kategori</th><th>Metrik</th>
    </tr>
    @foreach ($rooms as $room)
    <tr>
        <td>{{ $room['room_code'] }} - {{ $room['room_name'] }}</td>
        <td>{{ $room['room_type'] }}</td>
        <td>{{ $room['live_status'] }}</td>
        <td>{{ $room['scheduled_hours'] }} / {{ $room['scheduled_rate'] }}%</td>
        <td>{{ $room['actual_hours'] }}</td>
        <td>{{ $room['utilization_rate'] }}%</td>
        <td>{{ $room['ghost_bookings'] }} / {{ $room['approved_bookings'] }} ({{ $room['ghost_rate'] }}%)</td>
        <td>{{ $room['category'] }}</td>
        <td>@foreach ($room['metrics'] as $name => $value) {{ $name }}: {{ $value ?? '-' }}<br> @endforeach</td>
    </tr>
    @endforeach
</table>

<p><b>Underutilized:</b> {{ implode(', ', $underutilized) ?: '-' }}<br>
   <b>Overutilized:</b> {{ implode(', ', $overutilized) ?: '-' }}</p>
</body>
</html>
