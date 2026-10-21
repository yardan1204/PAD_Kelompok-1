<?php

namespace Tests\Unit;

use App\Models\AcademicUnit;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Strategies\AuditoriumUtilizationStrategy;
use App\Strategies\UtilizationCalculatorContext;
use App\Support\Utilization\UtilizationPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk fitur baru (eksperimen maintainability). Dibuat di file terpisah
 * agar file test lama tidak diubah sama sekali.
 *
 * Minggu uji: Senin 5 s.d. Jumat 9 Oktober 2026, jam tersedia 50 jam.
 */
class AuditoriumUtilizationStrategyTest extends TestCase
{
    use RefreshDatabase;

    private function auditorium(): Room
    {
        $unit = AcademicUnit::create(['code' => 'FRI', 'name' => 'Fakultas Rekayasa Industri']);
        $room = Room::create([
            'academic_unit_id' => $unit->id,
            'code' => 'AUD-01',
            'name' => 'Auditorium FRI',
            'building' => 'Gedung FRI',
            'capacity' => 200,
            'status' => 'available',
        ]);
        $room->forceFill(['type' => 'auditorium'])->save();

        return $room;
    }

    private function completedBooking(Room $room, string $start, string $end, int $people): void
    {
        $user = User::firstOrCreate(['email' => 'uji@cslus.test'], ['name' => 'Peminjam Uji', 'password' => 'password']);

        Booking::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'purpose' => 'Acara uji',
            'participant_count' => $people,
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'completed',
            'checked_in_at' => $start,
            'checked_out_at' => $end,
        ]);
    }

    public function test_auditorium_menimbang_jam_pakai_dengan_okupansi_kursi(): void
    {
        $room = $this->auditorium();
        $this->completedBooking($room, '2026-10-05 08:00:00', '2026-10-05 11:00:00', 150); // 3 jam x 75%
        $this->completedBooking($room, '2026-10-07 13:00:00', '2026-10-07 15:00:00', 100); // 2 jam x 50%

        $week = new UtilizationPeriod(CarbonImmutable::parse('2026-10-05'), CarbonImmutable::parse('2026-10-09'));
        $result = (new AuditoriumUtilizationStrategy())->calculate($room, $week);

        $this->assertSame(3.25, $result->metrics['seat_hours']);      // 2,25 + 1
        $this->assertSame(6.5, $result->utilizationRate);             // 3,25 / 50
        $this->assertSame(10.0, $result->metrics['raw_actual_rate']); // 5 / 50
    }

    public function test_context_mengenali_tipe_auditorium(): void
    {
        $context = $this->app->make(UtilizationCalculatorContext::class);

        $this->assertContains('auditorium', $context->supportedTypes());
        $this->assertInstanceOf(AuditoriumUtilizationStrategy::class, $context->strategyFor($this->auditorium()));
    }
}
