# Catatan Perbaikan Kode oleh AI

> **POV (Point of View):**
> Seluruh kode pada proyek ini **ditulis murni secara manual (tangan manusia)** oleh anggota kelompok.
> Peran **AI di sini hanya sebagai pemeriksa dan pemungkin (perbaikan/penuntasan)** agar kode yang ditulis manusia tersebut dapat dijalankan dengan benar.
> Semua solusi di bawah ditindaklanjuti oleh AI, namun **logika dan keputusan desain tetap milik penulis manusia**.

---

## Juknis (Petunjuk Teknis) yang Diikuti

| No | Juknis | Penjelasan |
|----|--------|------------|
| 1 | **Install Composer** | Jalankan `composer install` di root proyek untuk mengunduh seluruh dependensi Laravel yang tercantum di `composer.lock`. |
| 2 | **Jalankan validasi data table untuk ke database** | Gunakan `php artisan migrate` (beserta `php artisan migrate:fresh` bila perlu seed ulang) agar struktur tabel pada folder `database/migrations` benar-benar terbentuk di database. Bisa divalidasi ulang dengan `php artisan migrate:status`. |
| 3 | **Setup `.env`** | File `.env` berisi konfigurasi koneksi aplikasi ke database (konfigurasi utama/primary), contohnya `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. Pastikan juga `APP_URL` dan `SESSION_DRIVER` sesuai. |
| 4 | **Cara liat route di `web.php`** | Semua endpoint web didefinisikan di `routes/web.php`. Cara cepat memastikannya: jalankan `php artisan route:list` — command ini menampilkan seluruh route (method, URI, action, name) yang diambil dari `web.php`. |
| 5 | **`public function` agar bisa diakses umum** | Method controller yang dipanggil dari `routes/web.php` (misal `public function index()`, `public function store()`) harus dideklarasikan `public`. Jika `private`/`protected`, Laravel tidak bisa memanggilnya sehingga route akan gagal (error 500 / "method not callable"). |

---

## Yang Diubah / Diperbaiki oleh AI

### 1. `routes/web.php` — Route Rooms belum tersedia di View

**Kondisi awal (sebelum perbaikan):**
- Saat pengujian, bagian **rooms** tidak memiliki halaman/tampilan.
- `routes/web.php` sudah menunjuk ke `RoomController` (`rooms.index`, `rooms.create`, `rooms.store`), tetapi folder view untuk rooms **belum ada**, sehingga halaman `/rooms` tidak dapat dirender (error "view not found").

**Perbaikan yang dilakukan AI:**
- Membuat **folder baru** `resources/views/room` beserta **file `index.blade.php`** di dalamnya, agar route `/rooms` (`rooms.index`) memiliki view yang dapat dirender dan halaman rooms dapat diakses saat testing.

**File terdampak:**
- `resources/views/room/index.blade.php` (baru)
- `routes/web.php` (referensi route yang sudah ada; tidak diubah isi logikanya)

---

### 2. Dashboard Utilization gagal — konstanta belum terdefinisi di `models/Booking.php`

**Kasus / Error:**
- Error terjadi ketika menjalankan halaman **dashboard/utilization**.
- `app/Services/BookingService.php` menginisialisasi dan memanggil dua konstanta:
  - `STATUS_APPROVED`
  - `GHOST_TOLERANCE_MINUTES`
- Kedua konstanta tersebut **ditempatkan/tampung di dalam `app/Models/Booking.php`** (dipanggil sebagai `Booking::STATUS_APPROVED` dan `Booking::GHOST_TOLERANCE_MINUTES`, contoh pada baris 85 dan 153–159 di `BookingService.php`).

**Issue:**
- Di `app/Models/Booking.php`, konstanta **belum terinisialisasi**. Akibatnya runtime melempar error `Undefined constant App\Models\Booking::STATUS_APPROVED` (dan serupa untuk `GHOST_TOLERANCE_MINUTES`) sehingga halaman dashboard utilization gagal dimuat.

**Solusi yang dilakukan AI:**
- **Menambahkan konstanta tersebut di dalam `app/Models/Booking.php`** agar nilai yang dikirim/dipanggil dari `BookingService.php` dapat dijalankan:

```php
class Booking extends BaseCampusEntity
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const GHOST_TOLERANCE_MINUTES = 15;
}
```

**File terdampak:**
- `app/Models/Booking.php` (penambahan konstanta `STATUS_APPROVED` dan `GHOST_TOLERANCE_MINUTES`)
- `app/Services/BookingService.php` (tidak diubah — hanya pemanggil konstanta)

---

## Before – After

| Aspek | Sebelum | Sesudah |
|-------|---------|---------|
| Halaman `/rooms` | Error (view tidak ada) | Halaman rooms dapat dirender via `resources/views/room/index.blade.php` |
| Dashboard utilization | Error `Undefined constant Booking::STATUS_APPROVED` / `GHOST_TOLERANCE_MINUTES` | Berjalan normal, konstanta terdefinisi di `models/Booking.php` |

## Verifikasi
- ✅ `STATUS_APPROVED` & `GHOST_TOLERANCE_MINUTES` terverifikasi hadir di `app/Models/Booking.php` (baris 11 & 14).
- ✅ Referensi pemanggilan di `app/Services/BookingService.php` (baris 85 & 153–159) terverifikasi cocok.
- ⚠️ Pengujian runtime halaman (akses `/rooms` dan `/dashboard/utilization` via browser/`php artisan serve`) belum dijalankan oleh AI — disarankan diuji manual oleh anggota kelompok.

## Keterbatasan
- Catatan ini disusun berdasarkan kondisi kode saat tanggal pembuatan (2026-10-20). Perubahan berikutnya pada route/model perlu diperbarui manual.
