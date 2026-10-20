{{-- PLACEHOLDER BE (PAD-16): polos agar alur bisa dicoba. Silakan ganti oleh FE. --}}
<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><title>Ajukan Peminjaman</title></head>
<body>
<h1>Ajukan Peminjaman Ruang</h1>
@foreach ($errors->all() as $error) <p style="color:red">{{ $error }}</p> @endforeach

<form method="POST" action="{{ route('bookings.store') }}">
    @csrf
    <p><label>Ruangan
        <select name="room_id" required>
            @foreach ($rooms as $room)
                <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>
                    {{ $room->name }} ({{ $room->type }}, kapasitas {{ $room->capacity }})
                </option>
            @endforeach
        </select></label></p>
    <p><label>Keperluan <input name="purpose" value="{{ old('purpose') }}" required></label></p>
    <p><label>Jumlah peserta <input type="number" min="1" name="participant_count" value="{{ old('participant_count', 1) }}" required></label></p>
    <p><label>Mulai <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" required></label></p>
    <p><label>Selesai <input type="datetime-local" name="end_time" value="{{ old('end_time') }}" required></label></p>
    <button>Ajukan</button>
</form>
<p><a href="{{ route('bookings.index') }}">Kembali</a></p>
</body>
</html>
