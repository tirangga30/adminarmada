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
        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('license_number', 50)->nullable();
            $table->string('license_type', 30)->default('SIM A');
            $table->date('license_expiry')->nullable();
            $table->enum('status', ['available', 'delivering', 'off_duty', 'suspended'])->default('available')->index();
            $table->boolean('emergency_status')->default(false)->index();
            $table->text('emergency_note')->nullable();
            $table->decimal('current_latitude', 10, 7)->nullable();
            $table->decimal('current_longitude', 10, 7)->nullable();
            $table->timestamp('last_location_time')->nullable();
            $table->unsignedTinyInteger('battery_level')->nullable();
            $table->decimal('speed', 6, 2)->nullable();
            $table->decimal('heading', 6, 2)->nullable();
            $table->decimal('accuracy', 6, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_profiles');
    }
};
