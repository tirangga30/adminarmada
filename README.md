# Sistem Manajemen Distribusi Barang & Pelacakan Driver Realtime
## Web Admin Panel (Laravel)

> **Capstone Project — Jurusan Informatika, Fakultas Teknik, Universitas Siliwangi Tasikmalaya (2026)**
> * **Friza Tri Maulana** (237006125) - Backend & Security
> * **Tirangga Kamal Baskara** (237006141) - Frontend & UI/UX
> * **Gita Syafira Azzahra** (237006126) - Infrastructure & DevOps

Repository: [https://github.com/tirangga30/adminarmada](https://github.com/tirangga30/adminarmada)

---

### 1. Arsitektur Aktor & Hak Akses (Section 3.2 Proposal)

| Aktor | Platform | Fokus Utama | Hak Utama |
|---|---|---|---|
| **Admin** | **Web Panel (Sistem Ini)** | Administrasi & Master Control | Kelola akun/role, armada kendaraan, konfigurasi GPS/rute, audit log, support/override pengiriman. |
| **Owner** | **Mobile App (Flutter)** | Operasional Distribusi | Buat pengiriman, alokasi driver, live tracking rute, laporan performa. |
| **Driver** | **Mobile App (Flutter)** | Eksekusi Pengiriman | Terima order, navigasi GPS, update status delivery, kirim foto & tanda tangan POD. |

> **Catatan Penting:** Web Panel ini **khusus untuk Administrator**. Akun Owner dan Driver beroperasi secara mobile melalui REST API yang disediakan oleh backend ini.

---

### 2. Fitur-Fitur Utama Web Admin Panel

1. **Dashboard Operasional Realtime:**
   * Ringkasan KPI: Total Pengemudi (mengantar, siaga, off), Armada Kendaraan (beroperasi, siaga, servis), dan Status Pengiriman hari ini.
   * **Peta Live Armada (Leaflet.js & OpenStreetMap)** dengan penanda pergerakan driver aktif, kecepatan (km/h), persentase baterai smartphone, dan status pengiriman.
   * Banner Peringatan Darurat (SOS Driver) instan jika ada pengemudi mengalami kendala teknis/insiden di jalan.
   * Stream aktivitas sistem (*Audit Log*) dan tabel order pengiriman terkini.

2. **Live GPS Fleet Tracking (Pusat Komando):**
   * Tampilan peta pemantauan armada menyeluruh dengan polling otomatis setiap 10 detik.
   * Fitur **"Tampilkan Jejak Rute"** (*Breadcrumbs History*) untuk memvisualisasikan rute perjalanan yang telah dilalui driver beserta kecepatan dan baterai per titik koordinat.

3. **Manajemen Pengiriman & Intervensi (Delivery Support & Override):**
   * Alur status resmi: `ASSIGNED` &rarr; `ACCEPTED` &rarr; `EN_ROUTE` &rarr; `ARRIVED` &rarr; `UNLOADING` &rarr; `DELIVERED` (serta `FAILED` dan `CANCELLED`).
   * Detail order pengiriman dengan visualisasi rute interaktif, timeline pemberhentian (*waypoints/stops*), manifest barang bawaan, dan verifikasi bukti terima (*Proof of Delivery / POD* berupa tanda tangan digital & identitas penerima).
   * **Fitur Admin Support & Override:** Kemampuan darurat bagi Admin untuk mengintervensi status order, menugaskan ulang driver/kendaraan cadangan jika terjadi mogok/kendala, dan mencatat catatan alasan ke dalam audit log.

4. **Manajemen Pengemudi (Driver Management):**
   * Pencatatan data lisensi SIM (SIM A, B1, B1 Umum, C), masa berlaku, alokasi kendaraan, nomor telepon, dan status tugas.
   * Reset / tindak lanjut sinyal darurat (*SOS Alert*).

5. **Manajemen Armada Kendaraan (Vehicle Fleet):**
   * Pendataan plat nomor, merk/model, jenis armada (Blind Van, Pickup Box, Truk Engkel CDE, Truk Double CDD, Motor), kapasitas beban (kg & m³), bahan bakar, tahun armada, dan status servis berkala.

6. **Audit Log & Keamanan Sistem (F-ADM-03):**
   * Seluruh aktivitas sensitif (pendaftaran user, pergantian kendaraan, override pengiriman, pengubahan konfigurasi) tercatat otomatis lengkap dengan Aktor, Aksi, Target, Alamat IP, User Agent, dan metadata perubahan JSON.

7. **Konfigurasi Sistem (F-ADM-04):**
   * Pengaturan interval GPS smartphone (default: 10 detik).
   * Batas toleransi akurasi GPS (meter) untuk memfilter noise sinyal.
   * Pilihan provider rute (OSRM / Google Maps Routes API) dan algoritma optimasi rute.
   * Identitas hub logistik pusat dan nomor kontak hotline darurat.

8. **Laporan & Analitik Distribusi:**
   * Statistik keberhasilan pengiriman (*SLA Success Rate*), grafik distribusi status (Chart.js), ranking performa pengemudi, utilisasi armada, dan fitur cetak laporan (*printable*).

9. **Kelola Akun Pengguna (RBAC - F-ADM-01):**
   * Pembuatan dan penonaktifan akun Admin, Owner, dan Driver dengan enkripsi password Bcrypt.

---

### 3. REST API Endpoint untuk Aplikasi Mobile (Owner & Driver)

Backend Laravel ini telah siap dihubungkan ke aplikasi mobile (Flutter) melalui Laravel Sanctum:

* `POST /api/v1/auth/login` — Autentikasi dan penerbitan API Token.
* `GET  /api/v1/auth/profile` — Profil akun pengguna dan status driver.
* `POST /api/v1/auth/logout` — Pencabutan token.
* `GET  /api/v1/config` — Parameter konfigurasi sistem (interval GPS, hotline).
* `GET  /api/v1/driver/tasks` — Daftar order pengiriman yang ditugaskan ke driver.
* `POST /api/v1/driver/tasks/{id}/status` — Pembaruan status oleh driver (`ACCEPTED`, `EN_ROUTE`, `ARRIVED`, `UNLOADING`, `DELIVERED`, `FAILED`).
* `POST /api/v1/driver/tasks/{id}/pod` — Pengunggahan bukti pengiriman (nama penerima, tanda tangan digital, foto, koordinat).
* `POST /api/v1/driver/emergency` — Pengiriman sinyal SOS / darurat dari smartphone pengemudi.
* `POST /api/v1/tracking/location` — Pengiriman koordinat GPS berkala dari smartphone (`latitude`, `longitude`, `speed`, `heading`, `accuracy`, `battery_level`).

---

### 4. Panduan Menjalankan Sistem Secara Lokal

#### Persyaratan Sistem:
* PHP >= 8.2 (ekstensi `pdo_sqlite` dan `pdo_mysql` aktif)
* Composer
* Node.js & NPM

#### Langkah Instalasi & Menjalankan:

1. **Clone Repository & Masuk ke Folder:**
   ```bash
   cd c:\xampp\htdocs\armada
   ```

2. **Instal Dependensi:**
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Migrasi Database & Seeder Data Uji:**
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Jalankan Server Lokal:**
   ```bash
   php artisan serve
   ```
   Akses Web Panel di browser: `http://127.0.0.1:8000`

---

### 5. Akun Uji Coba (Bawaan Seeder)

* **Super Admin (Web Panel):**
  * Email: `admin@armada.test`
  * Password: `admin123`

* **Distributor Owner (Mobile App):**
  * Email: `owner@armada.test`
  * Password: `owner123`

* **Driver 1 (Mobile App):**
  * Email: `asep.driver@armada.test`
  * Password: `driver123`

* **Driver 2 (Mobile App):**
  * Email: `budi.driver@armada.test`
  * Password: `driver123`
