# Kontrak View ↔ Controller (FE-01, FE-02, FE-03)

Dokumen ini untuk anggota **Back-End**. Semua view Blade hanya *menampilkan* data; seluruh query,
rumus, dan aturan bisnis ada di Controller/Model/Service. Agar view tidak error, Controller wajib
mengirim variabel dan menyediakan route sesuai tabel di bawah.

## 1. Daftar file view

| File | Tugas | Dipanggil oleh |
|---|---|---|
| `layouts/app.blade.php` | FE-01 | semua halaman (`@extends('layouts.app')`) |
| `layouts/partials/flash.blade.php` | FE-01 | layout (notifikasi sukses/error) |
| `components/nav-link.blade.php` | FE-01 | layout (menu; hanya muncul jika route-nya ada) |
| `components/status-badge.blade.php` | FE-01 | badge status (warna via CSS `data-status`) |
| `components/progress-bar.blade.php` | FE-01 | bar persentase |
| `dashboard/index.blade.php` | FE-01 | `DashboardController@index` |
| `rooms/index.blade.php` | FE-02 | `RoomController@index` (sudah ada) |
| `rooms/create.blade.php` | FE-02 | `RoomController@create` (sudah ada) |
| `schedules/index.blade.php` | FE-03 | `ScheduleController@index` (sudah ada) |
| `bookings/index.blade.php` | FE-03 | `BookingController@index` |
| `bookings/create.blade.php` | FE-03 | `BookingController@create` |
| `bookings/partials/actions/{status}.blade.php` | FE-03 | dipilih otomatis sesuai `$booking->status` |

## 2. Route yang dibutuhkan

| Nama route | Method & URI (saran) | Keterangan |
|---|---|---|
| `dashboard` | `GET /dashboard` | Live Utilization Dashboard |
| `rooms.index`, `rooms.create`, `rooms.store` | sudah ada | – |
| `schedules.index`, `schedules.store` | sudah ada | – |
| `bookings.index` | `GET /bookings` | daftar booking |
| `bookings.create` | `GET /bookings/create` | form submit |
| `bookings.store` | `POST /bookings` | simpan → status `pending` |
| `bookings.approve` | `PATCH /bookings/{booking}/approve` | `pending` → `approved` |
| `bookings.checkin` | `PATCH /bookings/{booking}/check-in` | `approved` → `checked_in`, isi `checked_in_at` |
| `bookings.checkout` | `PATCH /bookings/{booking}/check-out` | `checked_in` → `completed`, isi `checked_out_at` |

Form approve/check-in/check-out mengirim `POST` + `@method('PATCH')` + `@csrf`.

## 3. Variabel per view

### `dashboard.index`
Semua angka **sudah dihitung dan dibulatkan** di Controller/Service (view tidak memanggil `round`/`number_format`).

```php
return view('dashboard.index', [
    'liveStatus' => ['AVAILABLE' => 3, 'BOOKED' => 1, 'OCCUPIED' => 2],
    'kpi' => [
        'scheduled_utilization' => 62.5,   // persen, angka saja
        'actual_utilization'    => 48.0,
        'effective_score'       => 76.8,
        'ghost_booking_count'   => 2,
        'category'              => 'UNDERUTILIZED', // UNDERUTILIZED | OPTIMAL | OVERUTILIZED
    ],
    'roomUtilizations' => [            // array/collection, satu baris per ruang
        [
            'room_code' => 'C301',
            'room_name' => 'Laboratorium C301',
            'live_status' => 'OCCUPIED',   // AVAILABLE | BOOKED | OCCUPIED
            'scheduled_utilization' => 75.0,
            'actual_utilization' => 50.0,
            'effective_score' => 66.7,
            'ghost_booking_count' => 1,
            'category' => 'OPTIMAL',
        ],
    ],
    'lastUpdated' => now()->format('d M Y, H:i:s'),
]);
```
Dashboard memuat ulang sendiri tiap 30 detik (`<meta http-equiv="refresh">`), jadi status ikut berubah setelah ada event check-in/check-out baru.

### `rooms.index`
- `$rooms` → paginator `Room`. **Mohon tambahkan relasi `facilities`** pada eager load agar tidak N+1:
  `Room::with(['academicUnit', 'facilities'])->orderBy('name')->paginate(10)`
- View memakai getter Model: `getEntitySummary()`, `getCapacity()`, `getStatus()`, dan untuk fasilitas `getQuantity()`, `getStatus()`, `getEntitySummary()`.

### `rooms.create`
- `$academicUnits` → collection `AcademicUnit` (sudah dikirim). Pastikan ada data unit akademik di seeder, karena belum ada halaman CRUD unit.

### `schedules.index`
- `$schedules` → paginator `Schedule` dengan `room` (sudah dikirim).
- `$rooms` → **opsional**. Jika dikirim (`Room::orderBy('name')->get()`), form "Tambah Jadwal" muncul di samping tabel.

### `bookings.index`
- `$bookings` → paginator `Booking` dengan relasi `room`, misal `Booking::with('room')->latest()->paginate(10)`.

### `bookings.create`
- `$rooms` → collection `Room`.
- `$roles` → array label role stakeholder, contoh
  `['Mahasiswa', 'Dosen', 'Admin Akademik', 'Kepala Laboratorium', 'Pimpinan Fakultas']`.

## 4. Kolom database yang dibutuhkan (tabel `bookings`)

Form booking mengirim field berikut. Migration `bookings` saat ini **belum** punya sebagian kolomnya,
jadi perlu migration tambahan:

| Field form / kolom | Tipe saran | Status di migration sekarang |
|---|---|---|
| `requester_name` | `string(150)` | **belum ada** |
| `requester_role` | `string(50)` | **belum ada** |
| `room_id` | FK | ada |
| `participant_count` | `unsignedInteger` | **belum ada** |
| `start_time`, `end_time` | `dateTime` | ada |
| `purpose` | `string(255)` | ada |
| `status` | `pending / approved / checked_in / completed / rejected` | ada |
| `checked_in_at`, `checked_out_at` | `dateTime nullable` (cast `datetime` di Model) | **belum ada** |
| `user_id` | FK | ada & wajib → jadikan `nullable()` atau isi dari user login |

Jangan lupa menambahkan kolom baru ke `$fillable` dan `casts()` di `app/Models/Booking.php`.

## 5. Cara mengirim pesan galat (bentrok jadwal / melebihi kapasitas)

View sudah menampilkan galat di dua tempat: kotak merah di atas halaman (dari layout) dan teks merah di bawah field.
Cukup kembalikan salah satu bentuk ini dari Controller:

```php
// Melebihi kapasitas -> galat di field participant_count
return back()->withInput()->withErrors([
    'participant_count' => "Jumlah peserta melebihi kapasitas {$room->name} ({$room->getCapacity()} orang).",
]);

// Bentrok jadwal -> galat di field start_time
return back()->withInput()->withErrors([
    'start_time' => "Jadwal bentrok: {$room->name} sudah dibooking pada rentang waktu tersebut.",
]);

// Alternatif umum (tanpa field tertentu)
return back()->withInput()->with('error', 'Pesan galat');
```

Pesan sukses: `redirect()->route(...)->with('success', 'Pesan')`.

> **Catatan saat uji coba:** input `datetime-local` mengirim format `2026-10-21T14:00` (pakai huruf `T`).
> Jika query bentrok membandingkan string ini langsung dengan kolom di SQLite, hasilnya salah
> (bentrok tidak terdeteksi). Ubah dulu ke Carbon: `Carbon::parse($data['start_time'])`.

## 6. Menambah status/aksi baru (≤ 2 file)

Tombol di kolom **Aksi** dipilih dari file `bookings/partials/actions/{status}.blade.php`.
Contoh menambah tombol *Reject* untuk status `pending`: cukup edit `pending.blade.php` dan tambah route-nya.
Contoh status baru `no_show`: buat `no_show.blade.php`. Status yang tidak punya file akan menampilkan `none.blade.php`.
Warna badge untuk status baru ditambahkan satu baris di `components/status-badge.blade.php`.
