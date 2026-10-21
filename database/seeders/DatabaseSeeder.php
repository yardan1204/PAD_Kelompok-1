<?php

namespace Database\Seeders;

use App\Models\AcademicUnit;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Data awal CSLUS yang meniru kondisi nyata peminjaman ruang di FRI.
 *
 * Data dibuat tetap (tidak acak) supaya angka utilisasi selalu sama setiap kali
 * `php artisan migrate:fresh --seed` dijalankan dan bisa dicek manual.
 * Pemakaian yang sudah terjadi diletakkan di MINGGU LALU (Senin-Jumat).
 * Lihat di dashboard: /dashboard/utilization?start=<Senin lalu>&end=<Jumat lalu>
 *
 * Gambaran kasus minggu lalu:
 * - Lab Komputer 1 : sering dipakai, tetapi ada 2 ghost booking (no_show).
 * - Lab Manufaktur : padat, semua booking terlaksana.
 * - Ruang Kelas 202: jarang dipakai, 1 ghost booking.
 *
 * Password semua akun: password
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $lastMonday = Carbon::now()->startOfWeek()->subWeek();
        $tomorrow = Carbon::tomorrow();

        // ------------------------------------------------------------------
        // 1. Pengguna per peran (borrower | lab_head | admin | leader)
        //    Akun "Test User" lama tetap dipertahankan.
        // ------------------------------------------------------------------
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => 'password']
        );

        $users = [];
        foreach ([
            'admin' => ['Admin Akademik FRI', 'admin@cslus.test', 'admin'],
            'kalab' => ['Kepala Laboratorium FRI', 'kalab@cslus.test', 'lab_head'],
            'dekan' => ['Wakil Dekan II FRI', 'pimpinan@cslus.test', 'leader'],
            'andi' => ['Andi Pratama', 'andi@cslus.test', 'borrower'],
            'sinta' => ['Sinta Rahmawati', 'sinta@cslus.test', 'borrower'],
            'hmti' => ['Ketua HMTI', 'hmti@cslus.test', 'borrower'],
        ] as $key => [$name, $email, $role]) {
            $users[$key] = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => 'password', 'role' => $role]
            );
        }

        // ------------------------------------------------------------------
        // 2. Unit akademik (FRI, FIF, FTE dari seeder lama tetap ada)
        // ------------------------------------------------------------------
        $fri = AcademicUnit::firstOrCreate(['code' => 'FRI'], ['name' => 'Fakultas Rekayasa Industri']);
        AcademicUnit::firstOrCreate(['code' => 'FIF'], ['name' => 'Fakultas Informatika']);
        AcademicUnit::firstOrCreate(['code' => 'FTE'], ['name' => 'Fakultas Teknik Elektro']);

        // ------------------------------------------------------------------
        // 3. Ruang dan laboratorium
        //    Kolom `type` belum masuk $fillable model Room, jadi diisi dengan
        //    forceFill agar tipe ruang (classroom/laboratory) tersimpan.
        // ------------------------------------------------------------------
        $rooms = [];
        foreach ([
            'labKom' => ['LAB-KOM-01', 'Laboratorium Komputer 1', 'laboratory', '2', 40, 'available', 'Lab praktikum pemrograman dan simulasi.'],
            'labMan' => ['LAB-MAN-01', 'Laboratorium Manufaktur', 'laboratory', '1', 30, 'available', 'Lab praktikum sistem produksi dan PPIC.'],
            'kls201' => ['KLS-201', 'Ruang Kelas 201', 'classroom', '2', 60, 'available', 'Ruang kuliah reguler.'],
            'kls202' => ['KLS-202', 'Ruang Kelas 202', 'classroom', '2', 40, 'available', 'Ruang kuliah reguler.'],
            'kls203' => ['KLS-203', 'Ruang Kelas 203', 'classroom', '2', 40, 'maintenance', 'Sedang perbaikan AC.'],
        ] as $key => [$code, $name, $type, $floor, $capacity, $status, $description]) {
            $room = Room::firstOrCreate(['code' => $code], [
                'academic_unit_id' => $fri->id,
                'name' => $name,
                'building' => 'Gedung FRI',
                'floor' => $floor,
                'capacity' => $capacity,
                'status' => $status,
                'description' => $description,
            ]);
            $room->forceFill(['type' => $type])->save();
            $rooms[$key] = $room;
        }

        // ------------------------------------------------------------------
        // 4. Fasilitas
        // ------------------------------------------------------------------
        foreach ([
            ['labKom', 'PC Core i5 RAM 16 GB', 40, 'available'],
            ['labKom', 'Proyektor', 1, 'available'],
            ['labMan', 'Mesin CNC mini', 2, 'available'],
            ['labMan', 'Conveyor simulasi lini produksi', 1, 'damaged'],
            ['labMan', 'Meja kerja', 10, 'available'],
            ['kls201', 'Proyektor', 1, 'available'],
            ['kls202', 'Proyektor', 1, 'maintenance'],
            ['kls203', 'AC', 2, 'maintenance'],
        ] as [$roomKey, $name, $quantity, $status]) {
            Facility::firstOrCreate(
                ['room_id' => $rooms[$roomKey]->id, 'name' => $name],
                ['quantity' => $quantity, 'status' => $status]
            );
        }

        // ------------------------------------------------------------------
        // 5. Jadwal kuliah dan praktikum minggu lalu
        //    [ruang, hari ke- (0 = Senin), mulai, selesai, judul]
        // ------------------------------------------------------------------
        foreach ([
            ['kls201', 0, '07:30', '10:00', 'Perancangan Aplikasi Dinamis'],
            ['kls201', 2, '10:30', '13:00', 'Statistika Industri'],
            ['kls202', 1, '13:00', '15:30', 'Basis Data'],
            ['labKom', 3, '07:30', '10:30', 'Praktikum Pemrograman'],
            ['labMan', 1, '13:00', '16:00', 'Praktikum PPIC'],
        ] as [$roomKey, $day, $start, $end, $title]) {
            Schedule::firstOrCreate([
                'room_id' => $rooms[$roomKey]->id,
                'title' => $title,
                'start_time' => $lastMonday->copy()->addDays($day)->setTimeFromTimeString($start),
            ], [
                'end_time' => $lastMonday->copy()->addDays($day)->setTimeFromTimeString($end),
            ]);
        }

        // ------------------------------------------------------------------
        // 6. Booking minggu lalu
        //    completed = terlaksana (ada check-in dan check-out)
        //    no_show   = ghost booking: disetujui tetapi peminjam tidak datang
        //    [ruang, peminjam, hari ke-, mulai, selesai, peserta, status,
        //     telat check-in (menit), pulang lebih awal (menit), tujuan]
        // ------------------------------------------------------------------
        foreach ([
            ['labKom', 'andi', 0, '10:00', '12:00', 25, 'completed', 5, 0, 'Kerja kelompok tugas besar'],
            ['labKom', 'sinta', 0, '13:00', '15:00', 18, 'completed', 0, 30, 'Latihan simulasi Arena'],
            ['labKom', 'hmti', 1, '08:00', '10:00', 30, 'no_show', null, null, 'Pelatihan Excel anggota HMTI'],
            ['labKom', 'andi', 2, '13:00', '16:00', 22, 'completed', 12, 0, 'Pengolahan data skripsi'],
            ['labKom', 'sinta', 4, '13:00', '15:00', 15, 'no_show', null, null, 'Asistensi praktikum'],
            ['labMan', 'andi', 0, '08:00', '11:00', 28, 'completed', 0, 0, 'Praktikum pengganti PPIC'],
            ['labMan', 'hmti', 2, '08:00', '12:00', 30, 'completed', 3, 0, 'Workshop lean manufacturing'],
            ['labMan', 'sinta', 3, '13:00', '16:00', 26, 'completed', 0, 15, 'Pengambilan data penelitian'],
            ['kls202', 'hmti', 1, '08:00', '10:00', 8, 'completed', 20, 30, 'Rapat pengurus HMTI'],
            ['kls202', 'andi', 3, '09:00', '11:00', 6, 'no_show', null, null, 'Belajar kelompok'],
        ] as [$roomKey, $userKey, $day, $start, $end, $people, $status, $late, $early, $purpose]) {
            $startAt = $lastMonday->copy()->addDays($day)->setTimeFromTimeString($start);
            $endAt = $lastMonday->copy()->addDays($day)->setTimeFromTimeString($end);

            Booking::firstOrCreate([
                'room_id' => $rooms[$roomKey]->id,
                'start_time' => $startAt,
            ], [
                'user_id' => $users[$userKey]->id,
                'requester_name' => $users[$userKey]->name,
                'purpose' => $purpose,
                'participant_count' => $people,
                'end_time' => $endAt,
                'status' => $status,
                'qr_token' => Str::random(32),
                'checked_in_at' => $status === 'completed' ? $startAt->copy()->addMinutes($late) : null,
                'checked_out_at' => $status === 'completed' ? $endAt->copy()->subMinutes($early) : null,
            ]);
        }

        // ------------------------------------------------------------------
        // 7. Booking yang akan datang
        // ------------------------------------------------------------------
        foreach ([
            ['labKom', 'andi', $tomorrow->copy()->setTime(10, 0), $tomorrow->copy()->setTime(12, 0), 8, 'approved', null, 'Kerja kelompok tugas besar PAD'],
            ['labMan', 'sinta', $tomorrow->copy()->addDay()->setTime(13, 0), $tomorrow->copy()->addDay()->setTime(15, 0), 20, 'pending', null, 'Asistensi praktikum PPIC'],
            ['kls201', 'hmti', $tomorrow->copy()->setTime(8, 0), $tomorrow->copy()->setTime(10, 0), 50, 'rejected', 'Pengajuan masuk kurang dari 24 jam sebelum kegiatan.', 'Seminar himpunan'],
        ] as [$roomKey, $userKey, $startAt, $endAt, $people, $status, $reason, $purpose]) {
            Booking::firstOrCreate([
                'room_id' => $rooms[$roomKey]->id,
                'start_time' => $startAt,
            ], [
                'user_id' => $users[$userKey]->id,
                'requester_name' => $users[$userKey]->name,
                'purpose' => $purpose,
                'participant_count' => $people,
                'end_time' => $endAt,
                'status' => $status,
                'qr_token' => $status === 'approved' ? Str::random(32) : null,
                'rejection_reason' => $reason,
            ]);
        }
    }
}
