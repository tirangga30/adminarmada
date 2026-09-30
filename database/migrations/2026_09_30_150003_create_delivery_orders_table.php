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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('title');
            $table->enum('status', [
                'ASSIGNED',
                'ACCEPTED',
                'EN_ROUTE',
                'ARRIVED',
                'UNLOADING',
                'DELIVERED',
                'FAILED',
                'CANCELLED'
            ])->default('ASSIGNED')->index();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('route_mode', ['manual', 'recommended', 'automatic'])->default('recommended');
            $table->string('origin_address')->default('Gudang Utama Tasikmalaya');
            $table->decimal('origin_latitude', 10, 7)->default(-7.3274);
            $table->decimal('origin_longitude', 10, 7)->default(108.2207);
            $table->decimal('total_distance_km', 8, 2)->nullable();
            $table->unsignedInteger('estimated_duration_minutes')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->text('failure_reason')->nullable();
            $table->text('admin_override_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
