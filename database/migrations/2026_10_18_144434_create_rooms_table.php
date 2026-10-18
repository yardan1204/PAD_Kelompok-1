<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_unit_id')
                ->constrained('academic_units')
                ->restrictOnDelete();

            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->string('building', 100);
            $table->string('floor', 30)->nullable();
            $table->unsignedInteger('capacity');
            $table->string('status', 30)->default('available');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};