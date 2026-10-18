<?php

namespace Tests\Unit;

use App\Models\AcademicUnit;
use App\Models\BaseCampusEntity;
use App\Models\Facility;
use App\Models\Room;
use App\Models\Schedule;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CampusEntityTest extends TestCase
{
    public function test_base_entity_is_abstract(): void
    {
        $reflection = new ReflectionClass(BaseCampusEntity::class);

        $this->assertTrue($reflection->isAbstract());
    }

    public function test_master_models_inherit_base_entity(): void
    {
        $models = [
            AcademicUnit::class,
            Room::class,
            Facility::class,
            Schedule::class,
        ];

        foreach ($models as $model) {
            $this->assertTrue(
                is_subclass_of($model, BaseCampusEntity::class)
            );
        }
    }

    public function test_room_rejects_negative_capacity(): void
    {
        $room = new Room();

        $this->expectException(InvalidArgumentException::class);

        $room->setCapacity(-5);
    }

    public function test_room_rejects_invalid_status(): void
    {
        $room = new Room();

        $this->expectException(InvalidArgumentException::class);

        $room->fill(['status' => 'invalid']);
    }

    public function test_facility_rejects_negative_quantity(): void
    {
        $facility = new Facility();

        $this->expectException(InvalidArgumentException::class);

        $facility->fill(['quantity' => -1]);
    }

    public function test_facility_rejects_invalid_status(): void
    {
        $facility = new Facility();

        $this->expectException(InvalidArgumentException::class);

        $facility->fill(['status' => 'invalid']);
    }

    public function test_polymorphism_returns_different_summaries(): void
    {
        $room = new Room();
        $room->name = 'Laboratorium C301';
        $room->setCapacity(30);

        $facility = new Facility();
        $facility->name = 'Proyektor';
        $facility->setQuantity(2);

        $entities = [$room, $facility];

        $this->assertCount(2, $entities);

        $this->assertSame(
            'Ruangan: Laboratorium C301, Kapasitas: 30 orang',
            $entities[0]->getEntitySummary()
        );

        $this->assertSame(
            'Fasilitas: Proyektor, Jumlah: 2 unit',
            $entities[1]->getEntitySummary()
        );
    }
}
