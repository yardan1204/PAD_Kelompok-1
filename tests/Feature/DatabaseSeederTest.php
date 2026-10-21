<?php

namespace Tests\Feature;

use App\Models\AcademicUnit;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Memastikan DatabaseSeeder berjalan tanpa error, berisi data kasus nyata,
 * dan seluruh halaman aplikasi bisa dibuka dengan data tersebut (bukti MVC
 * tersambung: route -> controller -> model -> view).
 */
class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_mengisi_data_master(): void
    {
        $this->seed();

        $this->assertDatabaseHas('academic_units', ['code' => 'FRI']);
        $this->assertSame(3, AcademicUnit::count());
        $this->assertSame(5, Room::count());
        $this->assertSame(7, User::count());
        $this->assertDatabaseHas('users', ['email' => 'kalab@cslus.test', 'role' => 'lab_head']);
    }

    public function test_seeder_mengisi_tipe_ruang_lab_dan_kelas(): void
    {
        $this->seed();

        $this->assertSame('laboratory', Room::where('code', 'LAB-KOM-01')->value('type'));
        $this->assertSame('laboratory', Room::where('code', 'LAB-MAN-01')->value('type'));
        $this->assertSame('classroom', Room::where('code', 'KLS-201')->value('type'));
    }

    public function test_seeder_berisi_booking_completed_approved_dan_ghost_booking(): void
    {
        $this->seed();

        $this->assertSame(7, Booking::where('status', 'completed')->count());
        $this->assertSame(1, Booking::where('status', 'approved')->count());
        $this->assertSame(3, Booking::where('status', 'no_show')->count());

        foreach (Booking::where('status', 'completed')->get() as $booking) {
            $this->assertNotNull($booking->checked_in_at);
            $this->assertNotNull($booking->checked_out_at);
            $this->assertTrue($booking->isGhostBooking() === false);
        }
    }

    public function test_seeder_aman_dijalankan_dua_kali(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(5, Room::count());
        $this->assertSame(13, Booking::count());
    }

    public function test_semua_halaman_bisa_dibuka_dengan_data_seeder(): void
    {
        $this->seed();

        foreach (['/', '/rooms', '/rooms/create', '/schedules', '/bookings', '/bookings/create', '/dashboard/utilization'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_dashboard_minggu_lalu_menampilkan_ruang_dari_seeder(): void
    {
        $this->seed();

        $monday = Carbon::now()->startOfWeek()->subWeek();

        $this->getJson('/dashboard/utilization?start=' . $monday->toDateString() . '&end=' . $monday->copy()->addDays(4)->toDateString())
            ->assertOk()
            ->assertJsonFragment(['room_code' => 'LAB-KOM-01'])
            ->assertJsonFragment(['room_code' => 'LAB-MAN-01']);
    }
}
