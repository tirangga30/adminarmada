<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number', 20)->unique();
            $table->string('brand_model');
            $table->enum('type', ['motorcycle', 'blind_van', 'pickup', 'truck_small', 'truck_heavy'])->default('blind_van');
            $table->decimal('capacity_weight_kg', 8, 2)->default(500.00);
            $table->decimal('capacity_volume_m3', 6, 2)->nullable();
            $table->string('fuel_type', 30)->default('Bensin');
            $table->unsignedSmallInteger('year')->default(2022);
            $table->enum('status', ['available', 'in_use', 'maintenance', 'inactive'])->default('available')->index();
            $table->date('last_maintenance_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
