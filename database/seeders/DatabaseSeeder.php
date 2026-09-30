<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\DeliveryItem;
use App\Models\DeliveryOrder;
use App\Models\DeliveryStop;
use App\Models\DriverProfile;
use App\Models\LocationEvent;
use App\Models\Message;
use App\Models\ProofOfDelivery;
use App\Models\SystemConfig;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Initial System Configurations
        $configs = [
            ['key' => 'company_name', 'label' => 'Nama Perusahaan', 'group' => 'company', 'value' => 'PT. Logistik Armada Priangan', 'type' => 'text', 'description' => 'Nama resmi entitas operasional logistik'],
            ['key' => 'company_address', 'label' => 'Alamat Kantor/Gudang Pusat', 'group' => 'company', 'value' => 'Jl. Siliwangi No. 24, Tasikmalaya, Jawa Barat', 'type' => 'text', 'description' => 'Titik pusat gudang/hub distribusi'],
            ['key' => 'company_phone', 'label' => 'Kontak Operasional', 'group' => 'company', 'value' => '0265-334589', 'type' => 'text', 'description' => 'Nomor hotline pengiriman'],
            ['key' => 'gps_interval_seconds', 'label' => 'Interval GPS Tracking (detik)', 'group' => 'tracking', 'value' => '10', 'type' => 'number', 'description' => 'Frekuensi pengiriman koordinat dari smartphone driver'],
            ['key' => 'gps_accuracy_threshold_meters', 'label' => 'Batas Akurasi GPS (meter)', 'group' => 'tracking', 'value' => '35', 'type' => 'number', 'description' => 'Toleransi akurasi GPS untuk memfilter noise sinyal'],
            ['key' => 'routing_provider', 'label' => 'Provider Peta & Rute', 'group' => 'routing', 'value' => 'OSRM', 'type' => 'select', 'description' => 'Layanan peta rute utama (OSRM / Google Maps API)'],
            ['key' => 'route_optimization_algorithm', 'label' => 'Algoritma Optimasi Rute', 'group' => 'routing', 'value' => 'Nearest-Neighbor TSP', 'type' => 'text', 'description' => 'Algoritma pengurutan waypoint tujuan otomatis'],
            ['key' => 'emergency_hotline', 'label' => 'Hotline Darurat Driver', 'group' => 'security', 'value' => '0812-9988-7766', 'type' => 'text', 'description' => 'Nomor darurat langsung untuk insiden driver'],
            ['key' => 'operating_hours_start', 'label' => 'Jam Mulai Operasional', 'group' => 'general', 'value' => '07:00', 'type' => 'text', 'description' => 'Jam dimulainya shift distribusi harian'],
            ['key' => 'operating_hours_end', 'label' => 'Jam Selesai Operasional', 'group' => 'general', 'value' => '18:00', 'type' => 'text', 'description' => 'Batas waktu akhir pengiriman normal'],
        ];

        foreach ($configs as $cfg) {
            SystemConfig::updateOrCreate(['key' => $cfg['key']], $cfg);
        }

        // 2. Users (Admin, Owner, Drivers)
        $admin = User::firstOrCreate(
            ['email' => 'admin@armada.test'],
            [
                'name' => 'Tirangga Kamal (Super Admin)',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '081223344550',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $owner = User::firstOrCreate(
            ['email' => 'owner@armada.test'],
            [
                'name' => 'Friza Tri Maulana (Distributor Owner)',
                'password' => Hash::make('owner123'),
                'role' => 'owner',
                'phone' => '081223344551',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 3. Vehicles Fleet
        $v1 = Vehicle::firstOrCreate(['plate_number' => 'Z 8492 KH'], [
            'brand_model' => 'Daihatsu Gran Max Blind Van 1.5',
            'type' => 'blind_van',
            'capacity_weight_kg' => 750,
            'capacity_volume_m3' => 3.8,
            'fuel_type' => 'Bensin Pertalite',
            'year' => 2023,
            'status' => 'in_use',
            'last_maintenance_at' => Carbon::now()->subDays(15),
            'notes' => 'Armada van logistik dalam kota, kondisi prima AC dingin',
        ]);

        $v2 = Vehicle::firstOrCreate(['plate_number' => 'Z 9104 TE'], [
            'brand_model' => 'Toyota Hilux Single Cabin Pickup',
            'type' => 'pickup',
            'capacity_weight_kg' => 1000,
            'capacity_volume_m3' => 4.2,
            'fuel_type' => 'Solar Dexlite',
            'year' => 2022,
            'status' => 'in_use',
            'last_maintenance_at' => Carbon::now()->subMonths(1),
            'notes' => 'Pickup bak terbuka dengan terpal tebal',
        ]);

        $v3 = Vehicle::firstOrCreate(['plate_number' => 'Z 7721 WP'], [
            'brand_model' => 'Isuzu Elf NLR 55 T - Box Pendingin',
            'type' => 'truck_small',
            'capacity_weight_kg' => 2500,
            'capacity_volume_m3' => 9.5,
            'fuel_type' => 'Solar',
            'year' => 2021,
            'status' => 'available',
            'last_maintenance_at' => Carbon::now()->subWeeks(2),
            'notes' => 'Truk engkel 4 roda untuk distribusi partai besar antarkota',
        ]);

        $v4 = Vehicle::firstOrCreate(['plate_number' => 'Z 3412 HG'], [
            'brand_model' => 'Honda Vario 160 Cargo',
            'type' => 'motorcycle',
            'capacity_weight_kg' => 60,
            'capacity_volume_m3' => 0.4,
            'fuel_type' => 'Bensin',
            'year' => 2024,
            'status' => 'available',
            'last_maintenance_at' => Carbon::now()->subDays(5),
            'notes' => 'Kurir ekspres dokumen dan paket farmasi kecil',
        ]);

        $v5 = Vehicle::firstOrCreate(['plate_number' => 'Z 8839 WA'], [
            'brand_model' => 'Mitsubishi Canter FE 71 Long',
            'type' => 'truck_heavy',
            'capacity_weight_kg' => 4500,
            'capacity_volume_m3' => 14.0,
            'fuel_type' => 'Solar',
            'year' => 2020,
            'status' => 'maintenance',
            'last_maintenance_at' => Carbon::now()->subDays(2),
            'notes' => 'Sedang perawatan berkala rem & ganti oli di bengkel resmi',
        ]);

        // 4. Drivers
        $driver1 = User::firstOrCreate(['email' => 'asep.driver@armada.test'], [
            'name' => 'Asep Saepuloh',
            'password' => Hash::make('driver123'),
            'role' => 'driver',
            'phone' => '082119882201',
            'is_active' => true,
        ]);

        DriverProfile::firstOrCreate(['user_id' => $driver1->id], [
            'vehicle_id' => $v1->id,
            'license_number' => 'SIM-A-9281729381',
            'license_type' => 'SIM A',
            'license_expiry' => Carbon::now()->addYears(3),
            'status' => 'delivering',
            'emergency_status' => false,
            'current_latitude' => -7.3325,
            'current_longitude' => 108.2250,
            'last_location_time' => Carbon::now()->subMinutes(1),
            'battery_level' => 88,
            'speed' => 38.5,
            'heading' => 125.0,
            'accuracy' => 8.2,
        ]);

        $driver2 = User::firstOrCreate(['email' => 'budi.driver@armada.test'], [
            'name' => 'Budi Santoso',
            'password' => Hash::make('driver123'),
            'role' => 'driver',
            'phone' => '082119882202',
            'is_active' => true,
        ]);

        DriverProfile::firstOrCreate(['user_id' => $driver2->id], [
            'vehicle_id' => $v2->id,
            'license_number' => 'SIM-B1-3829104821',
            'license_type' => 'SIM B1 Umum',
            'license_expiry' => Carbon::now()->addYears(2),
            'status' => 'delivering',
            'emergency_status' => false,
            'current_latitude' => -7.3481,
            'current_longitude' => 108.2120,
            'last_location_time' => Carbon::now()->subMinutes(3),
            'battery_level' => 74,
            'speed' => 24.0,
            'heading' => 210.0,
            'accuracy' => 11.5,
        ]);

        $driver3 = User::firstOrCreate(['email' => 'cecep.driver@armada.test'], [
            'name' => 'Cecep Ramdani',
            'password' => Hash::make('driver123'),
            'role' => 'driver',
            'phone' => '082119882203',
            'is_active' => true,
        ]);

        DriverProfile::firstOrCreate(['user_id' => $driver3->id], [
            'vehicle_id' => $v3->id,
            'license_number' => 'SIM-B1-7482910492',
            'license_type' => 'SIM B1 Umum',
            'license_expiry' => Carbon::now()->addYears(4),
            'status' => 'available',
            'emergency_status' => false,
            'current_latitude' => -7.3274,
            'current_longitude' => 108.2207,
            'last_location_time' => Carbon::now()->subMinutes(12),
            'battery_level' => 95,
            'speed' => 0.0,
            'heading' => 0.0,
            'accuracy' => 5.0,
        ]);

        $driver4 = User::firstOrCreate(['email' => 'dadan.driver@armada.test'], [
            'name' => 'Dadan Hendrayana',
            'password' => Hash::make('driver123'),
            'role' => 'driver',
            'phone' => '082119882204',
            'is_active' => true,
        ]);

        DriverProfile::firstOrCreate(['user_id' => $driver4->id], [
            'vehicle_id' => $v4->id,
            'license_number' => 'SIM-C-8291029481',
            'license_type' => 'SIM C',
            'license_expiry' => Carbon::now()->addYears(1),
            'status' => 'off_duty',
            'emergency_status' => false,
            'current_latitude' => -7.3210,
            'current_longitude' => 108.2315,
            'last_location_time' => Carbon::now()->subHours(2),
            'battery_level' => 60,
            'speed' => 0.0,
            'heading' => 0.0,
            'accuracy' => 12.0,
        ]);

        // 5. Delivery Orders
        // Order 1: Active En Route (Driver Asep)
        $do1 = DeliveryOrder::firstOrCreate(['order_number' => 'DO-20260930-001'], [
            'owner_id' => $owner->id,
            'driver_id' => $driver1->id,
            'vehicle_id' => $v1->id,
            'title' => 'Distribusi Sembako & Minyak Toko Retail Cihideung',
            'status' => 'EN_ROUTE',
            'priority' => 'high',
            'route_mode' => 'recommended',
            'origin_address' => 'Hub Pergudangan Siliwangi Tasikmalaya',
            'origin_latitude' => -7.3274000,
            'origin_longitude' => 108.2207000,
            'total_distance_km' => 14.8,
            'estimated_duration_minutes' => 45,
            'scheduled_at' => Carbon::now()->subHours(2),
            'started_at' => Carbon::now()->subMinutes(50),
        ]);

        $s1_1 = DeliveryStop::firstOrCreate(['delivery_order_id' => $do1->id, 'stop_sequence' => 1], [
            'customer_name' => 'Toko Subur Makmur',
            'customer_phone' => '081322119901',
            'address' => 'Jl. HZ Mustofa No. 112, Cihideung, Kota Tasikmalaya',
            'latitude' => -7.3325000,
            'longitude' => 108.2250000,
            'status' => 'completed',
            'arrived_at' => Carbon::now()->subMinutes(35),
            'completed_at' => Carbon::now()->subMinutes(20),
            'notes' => 'Barang diterima langsung oleh Pak Haji Subur di gudang belakang',
        ]);

        $s1_2 = DeliveryStop::firstOrCreate(['delivery_order_id' => $do1->id, 'stop_sequence' => 2], [
            'customer_name' => 'Minimarket Berkah Jaya',
            'customer_phone' => '081322119902',
            'address' => 'Jl. Nagarawangi No. 45, Cihideung, Kota Tasikmalaya',
            'latitude' => -7.3361000,
            'longitude' => 108.2285000,
            'status' => 'pending',
            'notes' => 'Harap konfirmasi lewat WA sebelum bongkar muatan',
        ]);

        $s1_3 = DeliveryStop::firstOrCreate(['delivery_order_id' => $do1->id, 'stop_sequence' => 3], [
            'customer_name' => 'Grosir Priangan Mandiri',
            'customer_phone' => '081322119903',
            'address' => 'Jl. KHZ Musthafa No. 280, Tugujaya, Kota Tasikmalaya',
            'latitude' => -7.3412000,
            'longitude' => 108.2320000,
            'status' => 'pending',
            'notes' => 'Pemberhentian terakhir rute Cihideung',
        ]);

        DeliveryItem::firstOrCreate(['delivery_order_id' => $do1->id, 'delivery_stop_id' => $s1_1->id, 'item_name' => 'Beras Pandan Wangi 25kg'], [
            'quantity' => 10,
            'unit' => 'Sak',
            'weight_kg' => 250.00,
            'notes' => 'Kemasan utuh, label segel biru',
        ]);

        DeliveryItem::firstOrCreate(['delivery_order_id' => $do1->id, 'delivery_stop_id' => $s1_1->id, 'item_name' => 'Minyak Goreng Sawit 2L'], [
            'quantity' => 20,
            'unit' => 'Karton',
            'weight_kg' => 240.00,
            'notes' => 'Isi 6 pouch per karton',
        ]);

        DeliveryItem::firstOrCreate(['delivery_order_id' => $do1->id, 'delivery_stop_id' => $s1_2->id, 'item_name' => 'Gula Pasir Kristal 50kg'], [
            'quantity' => 4,
            'unit' => 'Sak',
            'weight_kg' => 200.00,
            'notes' => 'Pastikan tidak kena basah/hujan',
        ]);

        DeliveryItem::firstOrCreate(['delivery_order_id' => $do1->id, 'delivery_stop_id' => $s1_3->id, 'item_name' => 'Tepung Terigu Segitiga 25kg'], [
            'quantity' => 8,
            'unit' => 'Sak',
            'weight_kg' => 200.00,
            'notes' => 'Batch produksi 2026',
        ]);

        // POD for Stop 1
        ProofOfDelivery::firstOrCreate(['delivery_order_id' => $do1->id, 'delivery_stop_id' => $s1_1->id], [
            'driver_id' => $driver1->id,
            'receiver_name' => 'Hj. Siti Aminah',
            'receiver_relation' => 'Pemilik Toko',
            'photo_path' => null,
            'signature_data' => 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="80"><path d="M10 50 Q 50 10 90 45 T 180 30" stroke="%233b82f6" fill="none" stroke-width="3"/></svg>',
            'notes' => 'Barang diterima lengkap 10 sak beras dan 20 karton minyak.',
            'latitude' => -7.3325000,
            'longitude' => 108.2250000,
            'delivered_at' => Carbon::now()->subMinutes(20),
        ]);

        // Order 2: Arrived at Singaparna (Driver Budi)
        $do2 = DeliveryOrder::firstOrCreate(['order_number' => 'DO-20260930-002'], [
            'owner_id' => $owner->id,
            'driver_id' => $driver2->id,
            'vehicle_id' => $v2->id,
            'title' => 'Pengiriman Bahan Pangan & Bumbu Dapur Pasar Singaparna',
            'status' => 'ARRIVED',
            'priority' => 'urgent',
            'route_mode' => 'automatic',
            'origin_address' => 'Hub Pergudangan Siliwangi Tasikmalaya',
            'origin_latitude' => -7.3274000,
            'origin_longitude' => 108.2207000,
            'total_distance_km' => 18.5,
            'estimated_duration_minutes' => 55,
            'scheduled_at' => Carbon::now()->subHours(3),
            'started_at' => Carbon::now()->subHours(1)->addMinutes(15),
        ]);

        $s2_1 = DeliveryStop::firstOrCreate(['delivery_order_id' => $do2->id, 'stop_sequence' => 1], [
            'customer_name' => 'Koperasi Unit Desa Singaparna',
            'customer_phone' => '085223399100',
            'address' => 'Jl. Pahlawan KHZ Mustofa, Singaparna, Kab. Tasikmalaya',
            'latitude' => -7.3481000,
            'longitude' => 108.2120000,
            'status' => 'arrived',
            'arrived_at' => Carbon::now()->subMinutes(8),
            'notes' => 'Driver sudah sampai di lokasi, sedang persiapan unloading',
        ]);

        DeliveryItem::firstOrCreate(['delivery_order_id' => $do2->id, 'delivery_stop_id' => $s2_1->id, 'item_name' => 'Bawang Merah Brebes 10kg'], [
            'quantity' => 15,
            'unit' => 'Karung Jaring',
            'weight_kg' => 150.00,
            'notes' => 'Sortir kualitas super',
        ]);

        DeliveryItem::firstOrCreate(['delivery_order_id' => $do2->id, 'delivery_stop_id' => $s2_1->id, 'item_name' => 'Bawang Putih Kating 20kg'], [
            'quantity' => 10,
            'unit' => 'Karung',
            'weight_kg' => 200.00,
            'notes' => 'Kering dan bersih',
        ]);

        // Order 3: Completed Yesterday
        $do3 = DeliveryOrder::firstOrCreate(['order_number' => 'DO-20260929-003'], [
            'owner_id' => $owner->id,
            'driver_id' => $driver3->id,
            'vehicle_id' => $v3->id,
            'title' => 'Distribusi Produk Frozen Food Kawalu & Cibeureum',
            'status' => 'DELIVERED',
            'priority' => 'medium',
            'route_mode' => 'recommended',
            'origin_address' => 'Hub Pergudangan Siliwangi Tasikmalaya',
            'origin_latitude' => -7.3274000,
            'origin_longitude' => 108.2207000,
            'total_distance_km' => 26.2,
            'estimated_duration_minutes' => 75,
            'scheduled_at' => Carbon::yesterday()->setHour(9),
            'started_at' => Carbon::yesterday()->setHour(9)->addMinutes(30),
            'completed_at' => Carbon::yesterday()->setHour(12)->addMinutes(45),
        ]);

        // Order 4: Assigned / Waiting Acceptance
        $do4 = DeliveryOrder::firstOrCreate(['order_number' => 'DO-20260930-004'], [
            'owner_id' => $owner->id,
            'driver_id' => $driver3->id,
            'vehicle_id' => $v3->id,
            'title' => 'Pengiriman Sembako Restoran Indihiang & Cisayong',
            'status' => 'ASSIGNED',
            'priority' => 'medium',
            'route_mode' => 'manual',
            'origin_address' => 'Hub Pergudangan Siliwangi Tasikmalaya',
            'origin_latitude' => -7.3274000,
            'origin_longitude' => 108.2207000,
            'total_distance_km' => 21.0,
            'estimated_duration_minutes' => 50,
            'scheduled_at' => Carbon::now()->addHours(1),
        ]);

        // 6. Location Events for Driver 1 (Asep) track
        $trackPoints = [
            [-7.3274, 108.2207, 0.0, 35],
            [-7.3288, 108.2215, 28.5, 30],
            [-7.3301, 108.2228, 34.0, 25],
            [-7.3315, 108.2239, 41.2, 18],
            [-7.3325, 108.2250, 15.0, 10],
        ];

        foreach ($trackPoints as $i => $pt) {
            LocationEvent::create([
                'driver_id' => $driver1->id,
                'delivery_order_id' => $do1->id,
                'latitude' => $pt[0],
                'longitude' => $pt[1],
                'speed' => $pt[2],
                'accuracy' => 7.5,
                'heading' => 135.0,
                'battery_level' => 88 - $i,
                'recorded_at' => Carbon::now()->subMinutes(40 - ($i * 7)),
            ]);
        }

        // 7. Audit Logs
        AuditLog::record(
            action: 'SYSTEM_BOOTSTRAP',
            targetType: 'SystemConfig',
            targetId: '1',
            description: 'Inisialisasi sistem manajemen armada & konfigurasi distribusi logistik berhasil dilakukan.',
            metadata: ['version' => '1.0.0', 'environment' => 'development'],
            actor: $admin
        );

        AuditLog::record(
            action: 'USER_REGISTERED',
            targetType: 'User',
            targetId: (string) $owner->id,
            description: "Akun Owner {$owner->name} diaktifkan dengan role distributor utama.",
            metadata: ['role' => 'owner', 'email' => $owner->email],
            actor: $admin
        );

        AuditLog::record(
            action: 'VEHICLE_ASSIGNED',
            targetType: 'Vehicle',
            targetId: (string) $v1->id,
            description: "Armada {$v1->plate_number} ({$v1->brand_model}) dialokasikan ke Driver {$driver1->name}.",
            metadata: ['driver_id' => $driver1->id, 'plate_number' => $v1->plate_number],
            actor: $admin
        );

        AuditLog::record(
            action: 'DELIVERY_DISPATCHED',
            targetType: 'DeliveryOrder',
            targetId: (string) $do1->id,
            description: "Order {$do1->order_number} diberangkatkan (status EN_ROUTE) oleh Driver {$driver1->name}.",
            metadata: ['status' => 'EN_ROUTE', 'order_number' => $do1->order_number],
            actor: $owner
        );

        // 8. Sample Operational Messages
        Message::create([
            'delivery_order_id' => $do1->id,
            'sender_id' => $owner->id,
            'receiver_id' => $driver1->id,
            'message' => 'Pak Asep, tolong prioritaskan Toko Subur Makmur dulu ya karena jam 12 gudang mereka tutup.',
            'message_type' => 'text',
            'is_read' => true,
        ]);

        Message::create([
            'delivery_order_id' => $do1->id,
            'sender_id' => $driver1->id,
            'receiver_id' => $owner->id,
            'message' => 'Siap Pak Owner, saat ini sudah selesai bongkar muatan di Toko Subur dan lanjut ke Nagarawangi.',
            'message_type' => 'text',
            'is_read' => true,
        ]);
    }
}
