# HAS Data Center

> **Industrial Environmental Monitoring & IIoT Management Platform**

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Active-brightgreen?style=flat-square)]()

HAS Data Center adalah platform pemantauan sensor lingkungan berbasis web yang dirancang untuk manajemen perangkat IIoT (Industrial Internet of Things) secara terpusat. Sistem ini menyediakan akuisisi data real-time, visualisasi multi-grafik, pelaporan otomatis, serta sistem pencatatan historis perawatan dan kalibrasi instrumen lapangan.

---

## Table of Contents

- [About the Project](#about-the-project)
- [Built With](#built-with)
- [System Architecture](#system-architecture)
- [Database Schema](#database-schema)
- [Features](#features)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Environment Configuration](#environment-configuration)
- [Usage](#usage)
  - [Role & Access Level](#role--access-level)
  - [Dashboard & Visualization](#dashboard--visualization)
  - [Data Export](#data-export)
  - [Automated Reporting](#automated-reporting)
  - [WhatsApp Notification](#whatsapp-notification)
- [API Reference](#api-reference)
- [Caching Strategy](#caching-strategy)
- [Roadmap](#roadmap)
- [License](#license)
- [Contact](#contact)

---

## About the Project

HAS Data Center dibangun untuk menjawab kebutuhan pemantauan parameter lingkungan industri yang tersebar secara geografis. Setiap *station* perangkat dapat terdiri dari beberapa sensor (suhu, kelembapan, tekanan, kecepatan angin, arah angin, dan parameter kustom lainnya) yang mengirimkan data secara berkala ke server pusat.

### Permasalahan yang Dipecahkan

| Tantangan | Solusi Implementasi |
|---|---|
| Data sensor tersebar di banyak lokasi lapangan | Agregasi terpusat dengan mapping GPS per perangkat |
| Kebutuhan visualisasi real-time dan historis | Dashboard multi-grafik (line, bar, wind rose) dengan filter rentang tanggal |
| Pelaporan periodik memerlukan intervensi manual | Sistem penjadwalan laporan otomatis (harian/mingguan/bulanan) via email |
| Kepatuhan dokumentasi kalibrasi & perawatan | Modul Syslog terstruktur dengan lampiran berkas |
| Kontrol akses multi-level untuk multi-tenant | RBAC granular berbasis perangkat dan kategori |
| Performa kueri data time-series yang tinggi | Strategi caching Redis berlapis dengan invalidasi otomatis |

### Alasan Pemilihan Teknologi

- **Laravel 11** dipilih karena ekosistemnya yang matang untuk aplikasi enterprise, dukungan asli terhadap queue, observer, middleware, dan task scheduling.
- **Redis** digunakan sebagai layer cache untuk mengurangi beban kueri terhadap tabel `tbl_data` yang bersifat append-only dan bervolume tinggi.
- **TailwindCSS + Vite** memungkinkan kompilasi aset yang cepat dengan utilitas CSS yang konsisten tanpa overhead dari framework CSS berbasis komponen.
- **Evolution API (WhatsApp)** dipilih sebagai alternatif notifikasi berbiaya rendah yang sudah familiar di lingkungan industri Indonesia dibandingkan SMS gateway konvensional.

---

## Built With

### Backend

| Teknologi | Versi | Fungsi |
|---|---|---|
| [Laravel](https://laravel.com) | ^11.31 | Web application framework |
| PHP | ^8.2 | Server-side runtime |
| MySQL / MariaDB | 8.0+ | Database relasional utama |
| Redis | 6.0+ | In-memory cache & session store |
| [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf) | ^3.1 | Generasi laporan PDF |
| [maatwebsite/excel](https://laravel-excel.com) | ^3.1 | Ekspor data ke Excel (.xlsx) |
| [Evolution API](https://evolution-api.com) | - | WhatsApp Business notification gateway |

### Frontend

| Teknologi | Versi | Fungsi |
|---|---|---|
| [TailwindCSS](https://tailwindcss.com) | ^3.4 | Utility-first CSS framework |
| [Vite](https://vitejs.dev) | ^6.0 | Build tool & asset bundler |
| Axios | ^1.7 | HTTP client untuk AJAX request |
| PostCSS | ^8.4 | CSS transformation pipeline |

### Infrastructure

| Teknologi | Fungsi |
|---|---|
| Nginx / Apache | Web server & reverse proxy |
| Supervisor | Process manager untuk queue worker |
| Cron Job | Task scheduling (auto-report) |

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         CLIENT LAYER                            │
│         Browser (Admin / User)  ◄──► WhatsApp Notification     │
└──────────────────────────┬──────────────────────────────────────┘
                           │ HTTPS
┌──────────────────────────▼──────────────────────────────────────┐
│                      LARAVEL APPLICATION                        │
│                                                                 │
│   ┌─────────────┐   ┌──────────────┐   ┌────────────────────┐  │
│   │   Routes    │──►│  Middleware  │──►│    Controllers     │  │
│   │  web.php    │   │  RBAC, Single│   │  Admin / User /    │  │
│   │             │   │  Session,    │   │  Auth Controllers  │  │
│   └─────────────┘   │  RateLimit   │   └────────┬───────────┘  │
│                     └──────────────┘            │              │
│   ┌─────────────────────────────────────────────▼───────────┐  │
│   │                     SERVICE LAYER                       │  │
│   │   SummaryReportService   │   WhatsAppService            │  │
│   └─────────────────────────┬───────────────────────────────┘  │
│                             │                                   │
│   ┌─────────────────────────▼───────────────────────────────┐  │
│   │                      MODEL LAYER                        │  │
│   │  DeviceModel │ SensorModel │ DataModel │ LatestDataModel │  │
│   │  UserModel   │ AccessModel │ LogsModel │ SyslogModel     │  │
│   └──────────┬──────────────────────────────────────────────┘  │
│              │                                                  │
│   ┌──────────▼──────────┐        ┌──────────────────────────┐  │
│   │    Observers        │        │       CacheHelper        │  │
│   │  Data / Device /    │───────►│  Redis Cache Invalidation│  │
│   │  Sensor / Access    │        │  Strategy                │  │
│   └─────────────────────┘        └──────────────────────────┘  │
└──────────────────────┬──────────────────────────────────────────┘
                       │
         ┌─────────────┴─────────────┐
         │                           │
┌────────▼──────────┐    ┌──────────▼──────────┐
│   MySQL Database  │    │    Redis Cache       │
│   tbl_data        │    │    device:{id}:*     │
│   tbl_latest_data │    │    user:{id}:*       │
│   tbl_device      │    │    TTL: 5 minutes    │
│   tbl_sensor      │    └─────────────────────┘
│   ...             │
└───────────────────┘
```

### Aliran Data IIoT

```
[Sensor/Perangkat Lapangan]
        │
        │ HTTP POST (API Key Auth)
        ▼
[Endpoint Ingestion Data]
        │
        ├──► tbl_data (time-series, append-only)
        ├──► tbl_latest_data (upsert, optimized read)
        └──► DataObserver → Cache Invalidation → Redis
```

---

## Database Schema

### Entity Relationship Overview

```
tbl_category ──── tbl_device ──── tbl_sensor ──── tbl_parameter
                      │                │
                      │                └──── tbl_data
                      │                └──── tbl_latest_data
                      │
                  tbl_access ──── tbl_user
                      │
                  tbl_syslog_header ──── tbl_syslog_detail ──── tbl_parameter
                      │
                  tbl_auto_report
                  tbl_logs
                  tbl_guidance
```

### Deskripsi Tabel Utama

| Tabel | Deskripsi | Field Kunci |
|---|---|---|
| `tbl_user` | Akun pengguna (admin & user) | `username`, `role`, `level`, `date_expired`, `api_key`, `whatsapp_number` |
| `tbl_device` | Perangkat/stasiun sensor | `device_id`, `device_name`, `location`, `latitude`, `longitude`, `device_ip`, `device_gap_timeout`, `device_hourly_data` |
| `tbl_sensor` | Konfigurasi sensor per perangkat | `device_id`, `sensor_name`, `parameter_name`, `sensor_unit`, `calibration_date`, `maintenance_date`, `status` |
| `tbl_parameter` | Definisi tipe pengukuran | `parameter_label`, `parameter_name`, `parameter_unit`, `parameter_indicator_min`, `parameter_indicator_max` |
| `tbl_data` | Data time-series (volume tinggi) | `device_id`, `parameter_name`, `value`, `timestamp` (unix), `recorded_at` |
| `tbl_latest_data` | Cache pembacaan terbaru | `device_id`, `parameter_name`, `value`, `timestamp` |
| `tbl_category` | Kategori pengelompokan perangkat | `category_name`, `category_description`, `category_icon` |
| `tbl_access` | Kontrol hak akses per perangkat | `device_id`, `category_id`, `user_id` |
| `tbl_auto_report` | Jadwal laporan otomatis | `device_id`, `schedule_report`, `email_report`, `auto_report` |
| `tbl_logs` | Log event sistem | `device_id`, `log_date`, `category`, `message`, `action`, `is_read_user`, `is_read_admin` |
| `tbl_syslog_header` | Log perawatan/kalibrasi/instalasi | `device_id`, `user_assigned`, `created_date`, `category` (enum), `note`, `linked_file` |
| `tbl_syslog_detail` | Detail item per syslog | `header_id`, `parameter_id`, `description` |
| `tbl_guidance` | Konten panduan pengguna | `title`, `description`, `image_path`, `content`, `link_path` |
| `sessions` | Manajemen sesi aktif | `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity` |

---

## Features

### Keamanan & Autentikasi
- **RBAC (Role-Based Access Control):** Dua peran utama (`admin`, `user`) masing-masing dengan level (`master`, `operator`)
- **Single Session Enforcement:** Login baru secara otomatis menginvalidasi sesi lama (hanya untuk peran `user`)
- **Rate Limiting:** Login dibatasi 5 percobaan/menit per IP; forgot password 3 percobaan/5 menit
- **Expired Account:** Akun pengguna non-admin dapat dikonfigurasi dengan tanggal kedaluwarsa
- **Secure Password Reset:** Alur reset kata sandi berbasis token dengan validasi email

### Manajemen Perangkat & Sensor
- CRUD perangkat dengan pemetaan lokasi GPS (latitude/longitude)
- Konfigurasi threshold per sensor: `parameter_indicator_min`, `parameter_indicator_max`, `parameter_indicator_alert`
- Tracking tanggal kalibrasi dan perawatan sensor
- Pembuatan sensor secara massal (*bulk creation*)

### Visualisasi & Dashboard
- **Line Chart:** Tren data historis per parameter
- **Bar Chart:** Perbandingan nilai antar periode
- **Wind Rose Chart:** Visualisasi khusus arah dan kecepatan angin
- **Progress Bar:** Indikator nilai terkini terhadap ambang batas
- **Maps Dashboard:** Peta lokasi perangkat dengan status terkini

### Ekspor & Pelaporan
- Ekspor data historis ke format **Excel (.xlsx)** dan **PDF**
- Pelaporan otomatis terjadwal: **harian**, **mingguan**, **bulanan**
- Pengiriman laporan via **email** dengan lampiran PDF
- Statistik ringkasan: rata-rata, minimum, maksimum beserta timestamp

### Notifikasi
- Notifikasi **WhatsApp** via Evolution API untuk event kritis
- Notifikasi dalam aplikasi dengan status **baca/belum baca** per peran

### Syslog & Dokumentasi Lapangan
- Pencatatan log tipe: `maintenance`, `calibration`, `installation`
- Lampiran file per entri log
- Detail per parameter sensor dalam setiap entri syslog

### Administrasi Sistem
- Manajemen kontrol akses granular (per perangkat/per kategori)
- Manajemen konten panduan pengguna (*guidance*)
- Cache statistics & manual cache purge

---

## Getting Started

### Prerequisites

Pastikan environment memenuhi persyaratan berikut sebelum memulai instalasi:

| Requirement | Versi Minimum | Keterangan |
|---|---|---|
| PHP | 8.2+ | Dengan ekstensi: `pdo`, `mbstring`, `xml`, `curl`, `zip`, `redis` |
| Composer | 2.x | Dependency manager PHP |
| Node.js | 18.x+ | Untuk build aset frontend |
| npm | 9.x+ | Node package manager |
| MySQL / MariaDB | 8.0+ | Database utama |
| Redis | 6.0+ | Cache store & session driver |
| Git | - | Version control |

Verifikasi instalasi:

```bash
php -v
composer --version
node -v
npm -v
mysql --version
redis-cli --version
```

### Installation

**1. Clone repositori**

```bash
git clone https://github.com/username/has-data-center.git
cd has-data-center
```

**2. Install dependensi PHP**

```bash
composer install --optimize-autoloader --no-dev
```

> Untuk environment development, hilangkan flag `--no-dev`.

**3. Install dependensi frontend**

```bash
npm install
```

**4. Salin dan konfigurasi file environment**

```bash
cp .env.example .env
```

Edit file `.env` sesuai konfigurasi lokal Anda (lihat bagian [Environment Configuration](#environment-configuration)).

**5. Generate application key**

```bash
php artisan key:generate
```

**6. Buat database dan jalankan migrasi**

```bash
# Buat database terlebih dahulu di MySQL
mysql -u root -p -e "CREATE DATABASE has_datacenter CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Jalankan migrasi
php artisan migrate

# (Opsional) Jalankan seeder untuk data awal
php artisan db:seed
```

**7. Build aset frontend**

```bash
# Production build
npm run build

# Development (watch mode)
npm run dev
```

**8. Konfigurasi storage symlink**

```bash
php artisan storage:link
```

**9. Optimasi aplikasi (Production)**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

**10. Konfigurasi Queue Worker**

Untuk automated reporting, queue worker harus berjalan. Konfigurasi menggunakan Supervisor:

```ini
# /etc/supervisor/conf.d/has-datacenter-worker.conf
[program:has-datacenter-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/has-data-center/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/html/has-data-center/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start has-datacenter-worker:*
```

**11. Konfigurasi Cron Job (Task Scheduling)**

```bash
crontab -e
```

Tambahkan entri berikut:

```cron
* * * * * cd /var/www/html/has-data-center && php artisan schedule:run >> /dev/null 2>&1
```

---

### Environment Configuration

Tabel di bawah menjelaskan seluruh variabel environment yang perlu dikonfigurasi:

#### Application

| Variabel | Contoh Nilai | Deskripsi |
|---|---|---|
| `APP_NAME` | `HAS Data Center` | Nama aplikasi yang tampil di UI |
| `APP_ENV` | `production` | Environment: `local`, `staging`, `production` |
| `APP_KEY` | *(generate via artisan)* | Application encryption key |
| `APP_DEBUG` | `false` | Aktifkan debug mode (nonaktifkan di production) |
| `APP_URL` | `https://datacenter.example.com` | Base URL aplikasi |
| `APP_TIMEZONE` | `Asia/Jakarta` | Timezone default server |

#### Database

| Variabel | Contoh Nilai | Deskripsi |
|---|---|---|
| `DB_CONNECTION` | `mysql` | Driver database |
| `DB_HOST` | `127.0.0.1` | Host database server |
| `DB_PORT` | `3306` | Port database |
| `DB_DATABASE` | `has_datacenter` | Nama database |
| `DB_USERNAME` | `has_user` | Username database |
| `DB_PASSWORD` | `strongpassword` | Password database |

#### Cache & Session

| Variabel | Contoh Nilai | Deskripsi |
|---|---|---|
| `CACHE_STORE` | `redis` | Driver cache: `redis`, `database` |
| `SESSION_DRIVER` | `database` | Driver session |
| `QUEUE_CONNECTION` | `database` | Driver queue |
| `REDIS_HOST` | `127.0.0.1` | Host Redis server |
| `REDIS_PORT` | `6379` | Port Redis |
| `REDIS_PASSWORD` | `null` | Password Redis (jika ada) |

#### Mail (SMTP)

| Variabel | Contoh Nilai | Deskripsi |
|---|---|---|
| `MAIL_MAILER` | `smtp` | Driver email |
| `MAIL_HOST` | `mail.example.com` | SMTP host |
| `MAIL_PORT` | `587` | Port SMTP (587 untuk TLS, 465 untuk SSL) |
| `MAIL_USERNAME` | `noreply@example.com` | Username SMTP |
| `MAIL_PASSWORD` | `mailpassword` | Password SMTP |
| `MAIL_ENCRYPTION` | `tls` | Enkripsi: `tls` atau `ssl` |
| `MAIL_FROM_ADDRESS` | `noreply@example.com` | Alamat pengirim email |
| `MAIL_FROM_NAME` | `HAS Data Center` | Nama pengirim email |

#### WhatsApp (Evolution API)

| Variabel | Contoh Nilai | Deskripsi |
|---|---|---|
| `EVOLUTION_BASE_URL` | `https://evolution.example.com` | URL base Evolution API server |
| `EVOLUTION_INSTANCE` | `has-datacenter` | Nama instance WhatsApp |
| `EVOLUTION_API_KEY` | `your-api-key` | API key Evolution API |

---

## Usage

### Role & Access Level

Sistem mengimplementasikan dua layer otorisasi:

**Layer 1 — Role:**

| Role | Deskripsi | Akses |
|---|---|---|
| `admin` | Administrator sistem | Manajemen penuh: perangkat, sensor, parameter, pengguna, akses, log, syslog, guidance |
| `user` | Pengguna akhir | Dashboard, visualisasi, ekspor data, laporan — hanya untuk perangkat yang ditetapkan |

**Layer 2 — Level (per Role):**

| Level | Berlaku untuk Role | Deskripsi |
|---|---|---|
| `master` | `admin` | Akses ke semua perangkat tanpa batasan kategori |
| `operator` | `admin` | Akses terbatas berdasarkan perangkat yang ditetapkan |
| `master` | `user` | Melihat semua perangkat yang ditetapkan padanya |
| `operator` | `user` | Akses dengan pembatasan tambahan |

Izin akses dapat dikonfigurasi secara granular melalui menu **Manage Access** di panel admin:

```
Admin Panel → Manage Access → Pilih User → Centang Device/Category yang diizinkan
```

### Dashboard & Visualisasi

Setelah login, pengguna diarahkan ke halaman home yang menampilkan ringkasan perangkat per kategori. Klik perangkat untuk membuka dashboard lengkap:

- **Line Chart:** Filter berdasarkan parameter dan rentang waktu
- **Bar Chart:** Agregasi harian/mingguan
- **Wind Rose:** Visualisasi distribusi arah & kecepatan angin
- **Maps Dashboard:** Peta interaktif lokasi seluruh perangkat

### Data Export

```
Dashboard → Historical Data → Pilih rentang tanggal → Export as Excel / Export as PDF
```

Format ekspor yang tersedia:
- **Excel (.xlsx):** Data mentah per parameter dengan kolom timestamp
- **PDF:** Laporan terformat dengan grafik dan tabel statistik ringkasan

### Automated Reporting

Konfigurasi laporan otomatis per perangkat:

```
Admin Panel → Manage Devices → Edit Device → Auto Report Settings
  - Schedule: daily | weekly | monthly
  - Email: alamat tujuan laporan
  - Status: aktif / nonaktif
```

Laporan akan digenerate oleh `SummaryReportService` dan dikirim via `AutoReportMail` sesuai jadwal yang dikonfigurasi.

### WhatsApp Notification

Notifikasi WhatsApp dikirim melalui Evolution API. Nomor tujuan dikonfigurasi per pengguna:

```
Admin Panel → Manage Users → Edit User → WhatsApp Number
```

Format nomor: format internasional tanpa tanda `+` (contoh: `628123456789`).

Contoh pengiriman notifikasi programatik:

```php
use App\Services\WhatsAppService;

$whatsapp = new WhatsAppService();
$whatsapp->sendText('628123456789', '*Alert!* Sensor *PM2.5* di Stasiun A melampaui ambang batas.');
```

---

## API Reference

Sistem menyediakan endpoint JSON yang digunakan secara internal oleh dashboard. Beberapa endpoint yang tersedia:

| Method | Endpoint | Deskripsi | Auth |
|---|---|---|---|
| `GET` | `/admin/devices-data` | Daftar perangkat dengan data terbaru | admin |
| `GET` | `/admin/device-latest-data/{deviceId}` | Pembacaan sensor terbaru per perangkat | admin |
| `GET` | `/admin/line-chart-data/{deviceId}` | Data untuk line chart | admin |
| `GET` | `/admin/bar-chart-data/{deviceId}` | Data untuk bar chart | admin |
| `GET` | `/admin/wind-rose-data/{deviceId}` | Data untuk wind rose chart | admin |
| `GET` | `/admin/historical-chart-data/{deviceId}` | Data historis untuk chart | admin |
| `GET` | `/admin/unread-notifications-count` | Jumlah notifikasi belum dibaca | admin |
| `GET` | `/user/devices-data` | Daftar perangkat yang dapat diakses user | user |
| `GET` | `/user/device-info/{deviceId}` | Detail informasi perangkat | user |

Semua endpoint menggunakan autentikasi session berbasis cookie (bukan token API publik).

---

## Caching Strategy

HAS Data Center mengimplementasikan strategi caching berlapis menggunakan Redis untuk memitigasi beban kueri pada tabel time-series bervolume tinggi.

### Pola Cache Key

```
device:{deviceId}:info               → Informasi perangkat
device:{deviceId}:chart:line:{param} → Data line chart per parameter
device:{deviceId}:chart:bar:{param}  → Data bar chart per parameter
device:{deviceId}:windrose           → Data wind rose
device:{deviceId}:historical:{param}:{start}:{end} → Data historis
device:{deviceId}:report             → Cache laporan ekspor
user:{userId}:devices:home           → Daftar perangkat di home user
```

### TTL Default: 5 menit

### Invalidasi Cache Otomatis

Cache diinvalidasi secara otomatis melalui **Eloquent Observers** ketika data berubah:

```
DataObserver    → tbl_data updated     → invalidateDeviceCharts()
DeviceObserver  → tbl_device updated   → invalidateAllDeviceCaches()
SensorObserver  → tbl_sensor updated   → invalidateSensorCache()
AccessObserver  → tbl_access updated   → invalidateUserDeviceCache()
```

### Manual Cache Purge (via Artisan)

```bash
php artisan cache:clear
```

---

## Roadmap

| Status | Fitur |
|---|---|
| ✅ Selesai | RBAC multi-level (admin/user, master/operator) |
| ✅ Selesai | Dashboard visualisasi real-time (line, bar, wind rose, maps) |
| ✅ Selesai | Ekspor data Excel & PDF |
| ✅ Selesai | Pelaporan otomatis terjadwal via email |
| ✅ Selesai | Notifikasi WhatsApp via Evolution API |
| ✅ Selesai | Syslog perawatan, kalibrasi & instalasi |
| ✅ Selesai | Redis caching dengan invalidasi otomatis via Observer |
| ✅ Selesai | Single session enforcement |
| ✅ Selesai | Rate limiting pada autentikasi |
| ✅ Selesai | Notifikasi ambang batas otomatis (threshold alerting) ke WhatsApp/Email |
| 🔄 Rencana | REST API publik dengan autentikasi API key untuk integrasi sistem eksternal |
| 🔄 Rencana | Dashboard analytics PROPER (Program Penilaian Peringkat Kinerja Perusahaan) |
| 🔄 Rencana | Mobile-responsive PWA (Progressive Web App) |
| 🔄 Rencana | Webhook outbound untuk integrasi SCADA / third-party systems |
| 🔄 Rencana | Multi-tenancy (isolasi data antar organisasi dalam satu instance) |

---

## License

Didistribusikan di bawah lisensi **MIT**. Lihat file [`LICENSE`](LICENSE) untuk informasi selengkapnya.

---

## Contact

**Project Maintainer**

Untuk pertanyaan teknis, laporan bug, atau kontribusi, silakan buka *issue* di repositori GitHub atau hubungi melalui:

- **GitHub Issues:** [github.com/username/has-data-center/issues](https://github.com/username/has-data-center/issues)
- **Email:** maintainer@example.com

---

<p align="center">Built with ❤️ using <a href="https://laravel.com">Laravel</a></p>
