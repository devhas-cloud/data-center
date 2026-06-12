# HAS Data Center

> **Industrial Environmental Monitoring & IIoT Management Platform**

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![Redis](https://img.shields.io/badge/Redis-6.0%2B-DC382D?style=flat-square&logo=redis&logoColor=white)](https://redis.io)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

HAS Data Center adalah platform pemantauan sensor lingkungan berbasis web yang dirancang untuk manajemen perangkat IIoT (Industrial Internet of Things) secara terpusat. Sistem ini menyediakan akuisisi data real-time, visualisasi multi-grafik (line, bar, wind rose, progress bar, maps), pelaporan otomatis via email dengan lampiran PDF, notifikasi WhatsApp untuk alert threshold, serta sistem pencatatan historis perawatan dan kalibrasi instrumen lapangan.

Platform ini mengelola aliran data *time-series* dari sensor-sensor lapangan (suhu, kelembapan, tekanan, kecepatan/arah angin, parameter kustom) yang dikirimkan secara periodik, di-cache dengan strategi Redis berlapis, dan divalidasi melalui RBAC multi-level.

---

## Table of Contents

- [About the Project](#about-the-project)
- [Built With](#built-with)
- [System Architecture](#system-architecture)
- [Database Schema](#database-schema)
- [Features](#features)
  - [Authentication & Security](#authentication--security)
  - [Role-Based Access Control](#role-based-access-control)
  - [Device & Sensor Management](#device--sensor-management)
  - [Dashboard & Visualization](#dashboard--visualization)
  - [Data Export & Reporting](#data-export--reporting)
  - [Automated Report Scheduling](#automated-report-scheduling)
  - [WhatsApp Threshold Alerting](#whatsapp-threshold-alerting)
  - [Syslog & Field Documentation](#syslog--field-documentation)
  - [Guidance Management](#guidance-management)
  - [Profile & Settings](#profile--settings)
- [Route Map (Web)](#route-map-web)
- [Artisan Commands](#artisan-commands)
- [Scheduled Tasks (Console)](#scheduled-tasks-console)
- [Elocuent Observers & Cache Invalidation](#observers--cache-invalidation)
- [Caching Strategy](#caching-strategy)
- [Mail System](#mail-system)
- [API Endpoints](#api-endpoints)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Environment Configuration](#environment-configuration)
- [Usage](#usage)
  - [Admin Panel](#admin-panel)
  - [User Dashboard](#user-dashboard)
- [Roadmap](#roadmap)
- [License](#license)
- [Contact](#contact)

---

## About the Project

HAS Data Center dibangun untuk menjawab kebutuhan pemantauan parameter lingkungan industri yang tersebar secara geografis. Setiap *station* perangkat dapat terdiri dari beberapa sensor (suhu, kelembapan, tekanan, kecepatan angin, arah angin, dan parameter kustom lainnya) yang mengirimkan data secara berkala ke server pusat.

### Permasalahan yang Dipecahkan

| Tantangan | Solusi Implementasi |
|---|---|
| Data sensor tersebar di banyak lokasi lapangan | Agregasi terpusat dengan mapping GPS per perangkat (`tbl_device.latitude`, `tbl_device.longitude`) |
| Kebutuhan visualisasi real-time dan historis | Dashboard multi-grafik (line, bar, wind rose, progress bar) dengan filter rentang tanggal dan parameter |
| Pelaporan periodik memerlukan intervensi manual | Sistem penjadwalan laporan otomatis (harian/mingguan/bulanan) via email dengan lampiran PDF |
| Notifikasi ambang batas sensor | WhatsApp alert otomatis via Evolution API dengan cooldown 1 jam per perangkat+pengguna |
| Kepatuhan dokumentasi kalibrasi & perawatan | Modul Syslog terstruktur (`tbl_syslog_header` + `tbl_syslog_detail`) dengan lampiran berkas |
| Kontrol akses multi-level untuk multi-tenant | RBAC granular berbasis role (`admin`/`user`), level (`master`/`operator`), dan akses per perangkat/kategori |
| Performa kueri data time-series yang tinggi | Strategi caching Redis berlapis dengan invalidasi otomatis via Eloquent Observers |
| Ekspor data dalam jumlah besar | Excel streaming via cursor() — O(1) memory terlepas dari ukuran dataset |

### Alasan Pemilihan Teknologi

- **Laravel 11** dipilih karena ekosistemnya yang matang untuk aplikasi enterprise, dukungan asli terhadap queue, observer, middleware, dan task scheduling.
- **Redis** digunakan sebagai layer cache untuk mengurangi beban kueri terhadap tabel `tbl_data` yang bersifat *append-only* dan bervolume tinggi.
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
| [maatwebsite/excel](https://laravel-excel.com) | ^3.1 | Ekspor data ke Excel (.xlsx) — *juga menggunakan PhpSpreadsheet native untuk streaming* |
| [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io) | ^2.x | Spreadsheet engine (digunakan langsung di `UserController::exportReportExcel`) |
| [Evolution API](https://evolution-api.com) | - | WhatsApp Business notification gateway |

### Frontend

| Teknologi | Versi | Fungsi |
|---|---|---|
| [TailwindCSS](https://tailwindcss.com) | ^3.4 | Utility-first CSS framework |
| [Vite](https://vitejs.dev) | ^6.0 | Build tool & asset bundler |
| Axios | ^1.7 | HTTP client untuk AJAX request |
| PostCSS | ^8.4 | CSS transformation pipeline |
| Chart.js (inferred) | - | Library grafik untuk line, bar, wind rose chart |

### Infrastructure

| Teknologi | Fungsi |
|---|---|
| Nginx / Apache | Web server & reverse proxy |
| Supervisor | Process manager untuk queue worker |
| Cron Job | Task scheduling (auto-report & WhatsApp alerts) |

---

## System Architecture

```
┌────────────────────────────────────────────────────────────────────────┐
│                          CLIENT LAYER                                  │
│          Browser (Admin / User)  ◄──► WhatsApp Notification           │
│          AJAX via Axios fetch JSON data                                │
└─────────────────────────────┬──────────────────────────────────────────┘
                              │ HTTPS
┌─────────────────────────────▼──────────────────────────────────────────┐
│                         LARAVEL APPLICATION                            │
│                                                                        │
│   ┌──────────────┐   ┌──────────────┐   ┌──────────────────────────┐  │
│   │   Routes     │──►│  Middleware  │──►│      Controllers         │  │
│   │  web.php     │   │  auth        │   │  AuthController          │  │
│   │  console.php │   │  role:admin  │   │  UserController          │  │
│   │              │   │  role:user   │   │  Admin*Controller        │  │
│   │              │   │  single.session│  │  (AdminHome/Dashboard/  │  │
│   │              │   │  throttle    │   │   Device/Sensor/Access/  │  │
│   │              │   │  RateLimiter │   │   User/Category/Parameter│  │
│   │              │   │              │   │   Syslog/Guidance/Logs)  │  │
│   └──────────────┘   └──────────────┘   └──────────┬───────────────┘  │
│                                                     │                  │
│   ┌──────────────────────────────────────────────────▼──────────────┐  │
│   │                      SERVICE LAYER                             │  │
│   │   WhatsAppService (Evolution API HTTP Client)                  │  │
│   │   SummaryReportService (PDF + Excel generation)                │  │
│   └────────────────────────────────────────┬────────────────────────┘  │
│                                            │                           │
│   ┌────────────────────────────────────────▼────────────────────────┐  │
│   │                         MODEL LAYER                            │  │
│   │  DeviceModel │ SensorModel │ DataModel │ LatestDataModel        │  │
│   │  UserModel   │ AccessModel │ ParameterModel │ CategoryModel     │  │
│   │  LogsModel   │ SyslogHeaderModel │ SyslogDetailModel           │  │
│   │  AutoReportModel │ GuidanceModel                               │  │
│   └────────────────────┬───────────────────────────────────────────┘  │
│                        │                                              │
│   ┌────────────────────▼────────────────┐  ┌───────────────────────┐  │
│   │          Eloquent Observers         │  │   CacheHelper         │  │
│   │  DataObserver / DeviceObserver      │──► Redis Cache           │  │
│   │  SensorObserver / AccessObserver    │  │ Invalidation Strategy │  │
│   └─────────────────────────────────────┘  └───────────────────────┘  │
└─────────────────────────┬─────────────────────────────────────────────┘
                          │
        ┌─────────────────┴──────────────────┐
        │                                    │
┌───────▼──────────┐              ┌──────────▼──────────┐
│   MySQL Database  │              │    Redis Cache       │
│   tbl_data        │              │    device:{id}:*     │
│   tbl_latest_data │              │    user:{id}:*       │
│   tbl_device      │              │    TTL: 1-5 minutes  │
│   tbl_sensor      │              └─────────────────────┘
│   ...             │
└───────────────────┘
```

### Aliran Data IIoT

```
[Sensor/Perangkat Lapangan]
        │
        │ HTTP POST (API Key Authentication)
        ▼
[Endpoint Ingestion Data]
        │
        ├──► tbl_data (time-series, append-only)
        ├──► tbl_latest_data (upsert, optimized read — 1 row per device+parameter)
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

| Tabel | Deskripsi | Kolom Kunci |
|---|---|---|
| `tbl_user` | Akun pengguna (admin & user) | `id`, `username`, `password`, `email`, `name`, `role` (`admin`/`user`), `level` (`master`/`operator`), `date_expired`, `api_key`, `whatsapp_number`, `timezone` |
| `tbl_device` | Perangkat/stasiun sensor | `id` (auto-increment), `device_id` (string unik), `device_name`, `device_category`, `location`, `district`, `latitude`, `longitude`, `device_ip`, `device_gap_timeout` (detik), `device_hourly_data`, `date_installation`, `linked_img`, `user_assigned` |
| `tbl_sensor` | Konfigurasi sensor per perangkat | `id`, `device_id`, `sensor_name`, `sensor_number`, `parameter_name`, `parameter_number`, `sensor_unit`, `parameter_indicator_min`, `parameter_indicator_max`, `parameter_indicator_alert`, `calibration_date`, `maintenance_date`, `status` (`active`/inactive), `notes` |
| `tbl_parameter` | Definisi tipe pengukuran | `id`, `parameter_label`, `parameter_name`, `parameter_unit`, `parameter_indicator_min`, `parameter_indicator_max` |
| `tbl_data` | Data time-series (volume tinggi) | `id`, `device_id`, `parameter_name`, `value`, `timestamp` (Unix), `recorded_at` (datetime) |
| `tbl_latest_data` | Cache pembacaan terbaru (1 baris per device+parameter) | `id`, `device_id`, `parameter_name`, `value`, `timestamp` |
| `tbl_category` | Kategori pengelompokan perangkat | `id`, `category_name`, `category_description`, `category_icon` |
| `tbl_access` | Kontrol hak akses per perangkat/kategori | `id`, `user_id`, `device_id` (FK ke `tbl_device.id`), `category_id` (FK ke `tbl_category.id`) |
| `tbl_auto_report` | Jadwal laporan otomatis | `id`, `device_id`, `schedule_report` (`daily`/`weekly`/`monthly`), `email_report`, `auto_report` (`Active`/`Inactive`) |
| `tbl_logs` | Log event sistem | `id`, `device_id`, `log_date`, `category`, `message`, `action`, `is_read_user` (boolean), `is_read_admin` (boolean), `created_at` |
| `tbl_syslog_header` | Header log perawatan/kalibrasi/instalasi | `id`, `device_id`, `user_assigned`, `created_date`, `category` (`maintenance`/`calibration`/`installation`), `note`, `linked_file` |
| `tbl_syslog_detail` | Detail item per syslog | `id`, `header_id`, `parameter_id` (FK ke `tbl_parameter.id`), `description` |
| `tbl_guidance` | Konten panduan pengguna | `id`, `title`, `description`, `image_path`, `content`, `link_path` |
| `sessions` | Manajemen sesi aktif (database driver) | `id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity` |
| `password_reset_tokens` | Token reset password | `username`, `token` (hashed), `created_at` |

---

## Features

### Authentication & Security

Implementasi terdapat pada `app/Http/Controllers/AuthController.php`:

- **RBAC (Role-Based Access Control):** Dua peran utama (`admin`, `user`) masing-masing dengan level (`master`, `operator`)
- **Single Session Enforcement:** Login baru secara otomatis menginvalidasi sesi lama — hanya untuk peran `user` (middleware `single.session`)
- **Rate Limiting:**
  - Login: maksimal 5 percobaan per menit per IP
  - Forgot password: maksimal 3 percobaan per 5 menit per IP
  - Reset password submit: maksimal 5 percobaan per 10 menit per IP
- **Expired Account:** Akun pengguna non-admin dapat dikonfigurasi dengan `date_expired` — login diblokir jika sudah kedaluwarsa
- **Secure Password Reset:** Alur reset password berbasis token acak (64 karakter) yang di-*hash* (bcrypt) sebelum disimpan di tabel `password_reset_tokens`
- **Validasi password:** Minimal 8 karakter, wajib mengandung huruf besar, huruf kecil, dan angka
- **Password hashing:** Semua password disimpan dengan bcrypt
- **Token expiry:** Token reset password kedaluwarsa setelah 60 menit
- **Logging keamanan:** Semua event login (sukses/gagal), logout, reset password dicatat ke log Laravel

### Role-Based Access Control

| Layer | Level | Deskripsi |
|---|---|---|
| **Role** | `admin` | Manajemen penuh: perangkat, sensor, parameter, pengguna, akses, syslog, guidance, logs |
| **Role** | `user` | Dashboard, visualisasi, ekspor data, laporan — hanya untuk perangkat yang ditetapkan |
| **Level (admin)** | `master` | Akses ke semua perangkat tanpa batasan |
| **Level (admin)** | `operator` | Akses terbatas berdasarkan perangkat yang ditetapkan |
| **Level (user)** | `master` | Melihat semua perangkat yang ditetapkan padanya |
| **Level (user)** | `operator` | Akses dengan pembatasan tambahan (misal: `advanced` untuk CRUD report) |

Izin akses dikelola melalui tabel `tbl_access` yang menghubungkan `user_id` dengan `device_id` dan/atau `category_id`.

### Device & Sensor Management

**Controllers:** `AdminDeviceController`, `AdminSensorController`

- **CRUD Perangkat:** Setiap perangkat memiliki `device_id` (string unik), nama, kategori, lokasi (GPS), IP, *gap timeout*, dan *hourly data* flag
- **Konfigurasi Threshold:** Setiap sensor memiliki `parameter_indicator_min`, `parameter_indicator_max`, dan `parameter_indicator_alert` untuk ambang batas notifikasi
- **Tracking Kalibrasi & Perawatan:** Kolom `calibration_date` dan `maintenance_date` pada setiap sensor
- **Bulk Sensor Creation:** Endpoint `POST /admin/sensors/bulk` untuk pembuatan sensor secara massal
- **Status Sensor:** Setiap sensor dapat diaktifkan/dinonaktifkan melalui kolom `status`

### Dashboard & Visualization

**Controllers:** `UserController`, `AdminDashboardController`

- **Line Chart:** Tren data historis per parameter (`/user/line-chart-data/{deviceId}?parameter=...`) — menampilkan data 24 jam terakhir dengan *gap detection* otomatis untuk data yang hilang
- **Bar Chart:** Agregasi per-jam dalam 24 jam terakhir (`/user/bar-chart-data/{deviceId}?parameter=...`)
- **Wind Rose Chart:** Visualisasi khusus arah dan kecepatan angin (`/user/wind-rose-data/{deviceId}`) — query agregat `wspeed` + `wdir` per menit
- **Progress Bar:** Indikator nilai terkini terhadap ambang batas minimum/maksimum (`/user/progress-bar/{deviceId}`)
- **Maps Dashboard:** Peta lokasi perangkat dengan status Online/Offline dan ikon kategori (`/user/maps-dashboard/{deviceId}`)
- **Historical Chart:** Grafik dengan rentang tanggal kustom dan parameter tertentu (`/user/historical-chart-data/{deviceId}`)

Semua endpoint menggunakan **Redis caching** dengan TTL 1-5 menit untuk performa optimal.

### Data Export & Reporting

**Controller:** `UserController`

#### Excel Export (`/user/export-report-excel`)

Implementasi ekspor Excel menggunakan **PhpSpreadsheet native** dengan arsitektur *streaming* via Laravel cursor():

- **Memory-efficient:** Menggunakan `cursor()` untuk mengambil data baris per baris — O(1) memory terlepas dari ukuran dataset
- **Fitur:**
  - Logo perusahaan di header
  - Informasi laporan (Device ID, Kategori, Parameter, Rentang Tanggal)
  - Total records count
  - Table header dengan kolom No, Date, Time, + kolom per parameter
  - Grouping data per menit (satu baris per menit)
  - Frozen header row
  - Conditional border styling (full border untuk ≤10.000 baris, header-only untuk lebih)
- **Proteksi:** Memory limit 2GB, time limit tidak terbatas
- **Output:** File `.xlsx` di-stream ke browser dan langsung dihapus setelah dikirim

#### PDF Export (`/user/export-report-pdf`)

Menggunakan **barryvdh/laravel-dompdf** untuk generate laporan PDF terformat dengan grafik dan tabel statistik ringkasan.

#### Export Record Count (`/user/export-report-count`)

Endpoint cepat untuk menghitung perkiraan jumlah baris sebelum ekspor — digunakan frontend untuk menampilkan peringatan jika dataset terlalu besar.

### Automated Report Scheduling

**Service:** `SummaryReportService` (inferred)  
**Mail:** `app/Mail/AutoReportMail.php`

| Schedule | Waktu Eksekusi | Cakupan Data |
|---|---|---|
| `daily` | Setiap hari pukul 07:00 | 24 jam sebelumnya (hari kemarin) |
| `weekly` | Setiap Senin pukul 08:00 | Satu minggu penuh (Senin–Minggu) |
| `monthly` | Tanggal 1 setiap bulan pukul 08:00 | Satu bulan kalender penuh |

**Fitur:**
- Laporan dikirim via email dengan lampiran **PDF** yang di-generate otomatis
- Subject email terformat: `[Auto Report] Daily Summary — {category} — {start} to {end}`
- Template email (`resources/views/emails/auto-report.blade.php`) menyertakan metadata laporan
- Konfigurasi per perangkat melalui tabel `tbl_auto_report` (schedule, email tujuan, status aktif/nonaktif)

### WhatsApp Threshold Alerting

**Service:** `app/Services/WhatsAppService.php`  
**Command:** `app/Console/Commands/SendWhatsAppAlerts.php`

#### Alur Kerja

```
1. Schedule berjalan setiap 2 menit
2. Query tbl_latest_data + tbl_sensor untuk mencari nilai yang melebihi threshold
3. Grouping per device_id
4. Cari user yang memiliki akses ke perangkat + nomor WhatsApp
5. Cek cooldown (1 jam per device+user)
6. Kirim pesan WhatsApp via Evolution API
7. Catat log ke tbl_logs (digunakan sebagai marker cooldown)
8. Delay acak 3-8 detik antar pengiriman
```

#### Format Pesan WhatsApp

```
*MONITORING SYSTEM ALERT*

*Alert Level* : HIGH
*Device* : {device_name}
*Time* : {datetime} WIB

*Threshold Violation Detected*

• {parameter}: {value} {unit} (Limit: {threshold} {unit})

*Recommended Actions*
1. Verify sensor operation.
2. Inspect equipment and site conditions.
3. Take corrective action if required.
4. Continue monitoring until values normalize.

Reference ID : ALT-{YYYYmmdd-Hisa}

_Automated Notification by Monitoring System_
```

#### Konfigurasi Environment

| Variabel | Deskripsi |
|---|---|
| `EVOLUTION_BASE_URL` | URL base Evolution API server |
| `EVOLUTION_INSTANCE` | Nama instance WhatsApp |
| `EVOLUTION_API_KEY` | API key untuk autentikasi |

#### Cooldown Logic

- Menggunakan tabel `tbl_logs` dengan category `whatsapp_alert`
- Jika ditemukan log dalam 1 jam terakhir untuk kombinasi device+user yang sama, pengiriman dilewati
- Mencegah notifikasi berulang untuk pelanggaran yang sama

### Syslog & Field Documentation

**Controllers:** `AdminSyslogController`, `UserController`

- **Tipe Log:** `maintenance`, `calibration`, `installation`
- **Header:** `tbl_syslog_header` — menyimpan device, user assigned, tanggal, kategori, notes, dan lampiran file
- **Detail:** `tbl_syslog_detail` — multiple item per header dengan referensi parameter dan deskripsi
- **Akses User:** User dapat melihat syslog perangkat yang diaksesnya melalui endpoint `/user/syslog-detail/{id}`

### Guidance Management

**Controllers:** `AdminGuidanceController`, `UserController`

- Admin: CRUD konten panduan (title, description, content, image, link)
- User: View daftar guidance via `/user/guidance`
- Berguna untuk dokumentasi operasional dan prosedur penggunaan sistem

### Profile & Settings

**Controller:** `UserController` (methods: `settings`, `updateProfile`, `changePassword`, `updateParameterAlerts`)

- **Update Profile:** Nama, email, nomor WhatsApp
- **Change Password:** Validasi password saat ini, minimal 6 karakter
- **Parameter Alerts:** User dapat mengatur ambang batas alert (`parameter_indicator_alert`) per sensor — divalidasi agar tidak kurang dari minimum atau lebih dari maksimum
- Semua data di-cache dengan TTL 10 menit

---

## Route Map (Web)

### Authentication Routes

| Method | URI | Controller Method | Middleware |
|---|---|---|---|
| GET/POST | `/login` | `AuthController@login` | guest |
| GET | `/logout` | `AuthController@logout` | auth |
| GET | `/forgot-password` | `AuthController@showForgotPasswordForm` | guest |
| POST | `/forgot-password` | `AuthController@sendResetLinkEmail` | guest |
| GET | `/reset-password/{token}` | `AuthController@showResetPasswordForm` | guest |
| POST | `/reset-password` | `AuthController@resetPassword` | guest |

### Admin Routes (prefix `/admin`, middleware: `auth`, `role:admin`)

| Group | Routes |
|---|---|
| **Home** | `/home`, `/devices-data`, `/device-latest-data/{deviceId}` |
| **Dashboard** | `/dashboard`, `/maps-dashboard/{deviceId}`, `/progress-bar/{deviceId}`, `/line-chart-data/{deviceId}`, `/bar-chart-data/{deviceId}`, `/wind-rose-data/{deviceId}`, `/historical-data`, `/historical-data/export`, `/historical-chart-data/{deviceId}` |
| **Users** | `/manage-users`, `/users` (CRUD), `/users/{id}/reset-api-key` |
| **Parameters** | `/manage-parameters`, `/parameters` (CRUD) |
| **Categories** | `/manage-categories`, `/categories` (CRUD) |
| **Sensors** | `/manage-sensors`, `/sensors` (CRUD), `/sensors/bulk` |
| **Devices** | `/manage-devices`, `/devices` (CRUD) |
| **Access** | `/manage-access`, `/access/{userId}` (GET/POST) |
| **Syslog** | `/manage-syslog`, `/syslog-data`, `/syslog/add`, `/syslog/store`, `/syslog/view/{id}`, `/syslog/edit/{id}`, `/syslog/update/{id}`, `/syslog/delete/{id}` |
| **Guidance** | `/manage-guidance`, `/guidance` (CRUD) |
| **Logs** | `/manage-logs`, `/logs-data`, `/logs/{id}` (PUT) |
| **Notifications** | `/unread-notifications-count`, `/mark-logs-read` |

### User Routes (prefix `/user`, middleware: `auth`, `role:user`, `single.session`)

| Group | Routes |
|---|---|
| **Home** | `/home`, `/devices-data` |
| **Dashboard** | `/dashboard`, `/maps-dashboard/{deviceId}`, `/progress-bar/{deviceId}`, `/line-chart-data/{deviceId}`, `/bar-chart-data/{deviceId}`, `/wind-rose-data/{deviceId}`, `/historical-data`, `/historical-data/export`, `/historical-chart-data/{deviceId}` |
| **Device Info** | `/device-info`, `/device-info/{deviceId}`, `/syslog-detail/{id}` |
| **Reports** | `/device-report`, `/get-device-report`, `/get-device-report/{id}`, `/save-device-report`, `/update-device-report`, `/delete-device-report/{id}` |
| **Report Data** | `/report-table-data`, `/report-hour-count`, `/report-hour-avg`, `/report-summary-data` |
| **Exports** | `/export-report-pdf`, `/export-report-excel`, `/export-report-count` |
| **Settings** | `/settings`, `/settings/update-profile`, `/settings/change-password`, `/settings/update-parameter-alerts` |
| **Guidance** | `/guidance` |
| **Logs** | `/logs-data`, `/user-devices`, `/mark-logs-read`, `/unread-notifications-count` |

---

## Artisan Commands

### `alerts:send-whatsapp`

**File:** `app/Console/Commands/SendWhatsAppAlerts.php`

Mengirim notifikasi WhatsApp ke pengguna ketika nilai sensor melebihi ambang batas alert.

**Signature:**
```bash
php artisan alerts:send-whatsapp
```

**Logika:**
1. Query `tbl_latest_data` JOIN `tbl_sensor` untuk menemukan parameter yang melebihi `parameter_indicator_alert`
2. Group berdasarkan device
3. Cari user dengan akses ke device + nomor WhatsApp
4. Cek cooldown 1 jam
5. Kirim pesan via WhatsAppService
6. Simpan log ke `tbl_logs`

### `reports:send-auto`

**Signature:**
```bash
php artisan reports:send-auto --type=daily
php artisan reports:send-auto --type=weekly
php artisan reports:send-auto --type=monthly
```

Mengirim laporan otomatis sesuai jadwal dengan lampiran PDF.

---

## Scheduled Tasks (Console)

Didefinisikan di `routes/console.php`:

| Schedule | Frequency | Command |
|---|---|---|
| Setiap hari pukul 07:00 | Daily | `reports:send-auto --type=daily` |
| Setiap Senin pukul 08:00 | Weekly | `reports:send-auto --type=weekly` |
| Tanggal 1 setiap bulan pukul 08:00 | Monthly | `reports:send-auto --type=monthly` |
| Setiap 2 menit | High frequency | `alerts:send-whatsapp` |

Konfigurasi Cron:
```bash
* * * * * cd /var/www/html/has-data-center && php artisan schedule:run >> /dev/null 2>&1
```

---

## Observers & Cache Invalidation

### DataObserver

**Triggers on:** `tbl_data` updated/created

**Actions:** Memanggil `invalidateDeviceCharts()` — membersihkan cache chart (line, bar, wind rose, historical) untuk device terkait.

### DeviceObserver

**Triggers on:** `tbl_device` updated

**Actions:** Memanggil `invalidateAllDeviceCaches()` — membersihkan semua cache yang berkaitan dengan device.

### SensorObserver

**Triggers on:** `tbl_sensor` updated

**Actions:** Memanggil `invalidateSensorCache()` — membersihkan cache data sensor untuk device terkait.

### AccessObserver

**Triggers on:** `tbl_access` updated

**Actions:** Memanggil `invalidateUserDeviceCache()` — membersihkan cache daftar perangkat untuk user yang terkait.

### Registrasi Observer

Observers didaftarkan di `App\Providers\AppServiceProvider`:

```php
DataModel::observe(DataObserver::class);
DeviceModel::observe(DeviceObserver::class);
SensorModel::observe(SensorObserver::class);
AccessModel::observe(AccessObserver::class);
```

---

## Caching Strategy

HAS Data Center mengimplementasikan strategi caching berlapis menggunakan Redis untuk memitigasi beban kueri pada tabel time-series bervolume tinggi.

### Pola Cache Key

```
device:{deviceId}:info                              → Informasi detail perangkat (TTL: 5 menit)
device:{deviceId}:maps                              → Data untuk maps dashboard (TTL: 1 menit)
device:{deviceId}:progress                          → Data progress bar (TTL: 1 menit)
device:{deviceId}:latest                            → Data terbaru perangkat (TTL: 2 menit)
device:{deviceId}:sensor:{parameter}                → Informasi sensor (TTL: 10 menit)
device:{deviceId}:chart:line:{parameter}            → Data line chart (TTL: 1 menit)
device:{deviceId}:chart:bar:{parameter}             → Data bar chart (TTL: 5 menit)
device:{deviceId}:windrose                          → Data wind rose (TTL: 5 menit)
device:{deviceId}:historical:{param}:{start}:{end}  → Data historical chart (TTL: 5 menit)
device:{deviceId}:report:{start}:{end}              → Data laporan (TTL: 5 menit)
device:{deviceId}:table-report-v2:{start}:{end}:page:{page} → Tabel laporan per halaman (TTL: 5 menit)
device:{deviceId}:table-report-v2:{start}:{end}:count       → Hitungan halaman laporan (TTL: 5 menit)
user:{userId}:devices:home                          → Daftar perangkat home user (TTL: 2 menit)
user:{userId}:devices:dashboard                     → Data dashboard user (TTL: 5 menit)
user:{userId}:device-info                           → Data device info user (TTL: 5 menit)
user:{userId}:device-report                         → Data halaman report user (TTL: 5 menit)
user:{userId}:settings                              → Data settings user (TTL: 10 menit)
user:{userId}:devices-list                          → Daftar device untuk filter (TTL: 10 menit)
user:{userId}:logs:{md5(params)}                    → Data logs dengan filter (TTL: 1 menit)
```

### Invalidasi Cache Otomatis

| Observer | Trigger | Cache Key yang Diinvalidasi |
|---|---|---|
| `DataObserver` | Data baru masuk | `device:{id}:chart:*`, `device:{id}:windrose`, `device:{id}:progress`, `device:{id}:maps` |
| `DeviceObserver` | Device diupdate | Semua cache yang mengandung `device:{id}` |
| `SensorObserver` | Sensor diupdate | `device:{id}:sensor:*` |
| `AccessObserver` | Hak akses berubah | `user:{id}:devices:*`, `user:{id}:device-info`, `user:{id}:dashboard` |

### Manual Cache Purge

```bash
# Flush semua cache
php artisan cache:clear

# Atau hapus kunci spesifik via Redis CLI
redis-cli KEYS "device:DEV-001:*" | xargs redis-cli DEL
```

---

## Mail System

### AutoReportMail (`app/Mail/AutoReportMail.php`)

Mailable untuk pengiriman laporan otomatis via email.

- **Queueable:** Menggunakan queue untuk menghindari blocking pada request
- **Attachment:** PDF laporan di-generate dan dilampirkan secara dinamis
- **Subject:** `[Auto Report] {type} Summary — {category} — {start} to {end}`
- **View:** `resources/views/emails/auto-report.blade.php`
- **Parameters yang dikirim ke view:** `deviceId`, `deviceCategory`, `scheduleType`, `startDate`, `endDate`, `generatedAt`

### ResetPasswordMail (`app/Mail/ResetPasswordMail.php`)

Mailable untuk pengiriman link reset password.

- **Subject:** `Reset Password - HAS`
- **URL:** `{APP_URL}/reset-password/{token}?username={username}`
- **View:** `resources/views/emails/reset-password.blade.php`

---

## API Endpoints

Sistem menyediakan endpoint JSON yang digunakan secara internal oleh dashboard via AJAX/Axios.

### Admin Endpoints (JSON)

| Method | URI | Deskripsi |
|---|---|---|
| `GET` | `/admin/devices-data` | Daftar perangkat dengan data terbaru |
| `GET` | `/admin/device-latest-data/{deviceId}` | Pembacaan sensor terbaru per perangkat |
| `GET` | `/admin/line-chart-data/{deviceId}?parameter=` | Data untuk line chart |
| `GET` | `/admin/bar-chart-data/{deviceId}?parameter=` | Data untuk bar chart |
| `GET` | `/admin/wind-rose-data/{deviceId}` | Data untuk wind rose chart (wspeed + wdir) |
| `GET` | `/admin/historical-chart-data/{deviceId}?parameter=&start_date=&end_date=` | Data historis untuk chart |
| `GET` | `/admin/historical-data` | Tabel data historis |
| `GET` | `/admin/unread-notifications-count` | Jumlah notifikasi belum dibaca |

### User Endpoints (JSON)

| Method | URI | Deskripsi |
|---|---|---|
| `GET` | `/user/devices-data` | Daftar perangkat milik user (cached) |
| `GET` | `/user/device-info/{deviceId}` | Detail perangkat + konfigurasi sensor + syslog |
| `GET` | `/user/maps-dashboard/{deviceId}` | Data peta lokasi perangkat |
| `GET` | `/user/progress-bar/{deviceId}` | Progress bar parameter |
| `GET` | `/user/line-chart-data/{deviceId}?parameter=` | Data line chart |
| `GET` | `/user/bar-chart-data/{deviceId}?parameter=` | Data bar chart |
| `GET` | `/user/wind-rose-data/{deviceId}` | Data wind rose |
| `GET` | `/user/historical-chart-data/{deviceId}?parameter=&start_date=&end_date=` | Data historical chart |
| `POST` | `/user/save-device-report` | Buat jadwal report baru |
| `POST` | `/user/update-device-report` | Update jadwal report |
| `DELETE` | `/user/delete-device-report/{id}` | Hapus jadwal report |
| `GET` | `/user/report-table-data?device_id=&start_date=&end_date=&page=` | Tabel data laporan (paginasi) |
| `GET` | `/user/report-hour-count?device_id=&start_date=&end_date=` | Ringkasan count per jam |
| `GET` | `/user/report-hour-avg?device_id=&start_date=&end_date=` | Rata-rata per jam per parameter |
| `GET` | `/user/report-summary-data?device_id=&start_date=&end_date=` | Statistik per parameter (avg/max/min + timestamp) |
| `GET` | `/user/export-report-count?device_id=&start_date=&end_date=` | Hitungan baris ekspor |
| `GET` | `/user/export-report-excel?device_id=&start_date=&end_date=` | Download Excel |
| `GET` | `/user/export-report-pdf?device_id=&start_date=&end_date=` | Download PDF |
| `GET` | `/user/syslog-detail/{id}` | Detail syslog |
| `GET` | `/user/guidance` | Daftar panduan |
| `GET` | `/user/logs-data` | Data log dengan filter |
| `GET` | `/user/user-devices` | Daftar perangkat untuk filter |
| `GET` | `/user/unread-notifications-count` | Jumlah notifikasi belum dibaca |
| `POST` | `/user/settings/update-profile` | Update profil |
| `POST` | `/user/settings/change-password` | Ganti password |
| `POST` | `/user/settings/update-parameter-alerts` | Update ambang batas alert sensor |

Semua endpoint menggunakan autentikasi session berbasis cookie (bukan token API publik).

---

## Getting Started

### Prerequisites

Pastikan environment memenuhi persyaratan berikut:

| Requirement | Versi Minimum | Keterangan |
|---|---|---|
| PHP | 8.2+ | Ekstensi: `pdo`, `mbstring`, `xml`, `curl`, `zip`, `gd`, `redis`, `bcmath`, `fileinfo`, `openssl`, `tokenizer`, `json` |
| Composer | 2.x | Dependency manager PHP |
| Node.js | 18.x+ | Untuk build aset frontend |
| npm | 9.x+ | Node package manager |
| MySQL / MariaDB | 8.0+ | Database utama |
| Redis | 6.0+ | Cache store & session driver |
| Git | - | Version control |
| Supervisor | - | Process manager untuk queue worker |
| Nginx / Apache | - | Web server |

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
Edit file `.env` sesuai konfigurasi lokal Anda.

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
; /etc/supervisor/conf.d/has-datacenter-worker.conf
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

Tambahkan:
```cron
* * * * * cd /var/www/html/has-data-center && php artisan schedule:run >> /dev/null 2>&1
```

---

### Environment Configuration

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
| `SESSION_DRIVER` | `database` | Driver session (tabel `sessions`) |
| `QUEUE_CONNECTION` | `database` | Driver queue (tabel `jobs`) |
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

### Admin Panel

Akses admin setelah login akan diarahkan ke `/admin/home`.

**Fitur Admin:**
- **Home:** Overview perangkat per kategori dengan data terbaru
- **Dashboard:** Visualisasi lengkap (line chart, bar chart, wind rose, maps, progress bar, historical data + export)
- **Manage Users:** CRUD pengguna, reset API key, atur nomor WhatsApp, atur tanggal kedaluwarsa
- **Manage Parameters:** CRUD definisi parameter pengukuran
- **Manage Categories:** CRUD kategori perangkat
- **Manage Sensors:** CRUD sensor per perangkat, bulk creation
- **Manage Devices:** CRUD perangkat, konfigurasi auto report
- **Manage Access:** Kontrol akses granular per user ke perangkat/kategori
- **Manage Syslog:** Catat maintenance, kalibrasi, instalasi dengan lampiran
- **Manage Guidance:** Atur konten panduan pengguna
- **Manage Logs:** Lihat seluruh event sistem, tandai sudah dibaca

### User Dashboard

Akses user setelah login akan diarahkan ke `/user/home`.

**Fitur User:**
- **Home:** Overview perangkat yang diizinkan diakses, per kategori
- **Dashboard:** Grafik real-time + maps lokasi perangkat
- **Device Info:** Detail perangkat (spesifikasi, konfigurasi sensor, riwayat syslog)
- **Report:** Buat/kelola jadwal auto report, lihat data tabel, download Excel/PDF
- **Settings:** Update profil, ganti password, atur ambang batas alert
- **Guidance:** Lihat panduan penggunaan sistem
- **Logs:** Lihat log event perangkat yang diakses

#### Workflow Report User

1. Buka menu **Device Report**
2. Pilih perangkat dari dropdown
3. Atur rentang tanggal
4. Lihat data dalam bentuk tabel (dengan pagination 720 baris per halaman)
5. Export ke Excel atau PDF
6. Atau buat jadwal auto report (daily/weekly/monthly)

---

## Roadmap

| Status | Fitur |
|---|---|
| ✅ Selesai | RBAC multi-level (admin/user, master/operator) |
| ✅ Selesai | Dashboard visualisasi real-time (line, bar, wind rose, maps, progress bar) |
| ✅ Selesai | Ekspor data Excel (streaming, memory-efficient) & PDF |
| ✅ Selesai | Pelaporan otomatis terjadwal via email dengan lampiran PDF |
| ✅ Selesai | Notifikasi WhatsApp via Evolution API (dengan cooldown 1 jam) |
| ✅ Selesai | Syslog perawatan, kalibrasi & instalasi |
| ✅ Selesai | Redis caching dengan invalidasi otomatis via Observer |
| ✅ Selesai | Single session enforcement (user role) |
| ✅ Selesai | Rate limiting pada autentikasi (login, forgot password, reset password) |
| ✅ Selesai | Notifikasi ambang batas otomatis (threshold alerting) ke WhatsApp |
| ✅ Selesai | Ringkasan statistik per parameter (avg/max/min + timestamp) |
| ✅ Selesai | Progress bar indikator nilai terhadap threshold |
| ✅ Selesai | Edit profil & ganti password dari sisi user |
| ✅ Selesai | Update ambang batas alert dari sisi user |
| ✅ Selesai | Gap detection otomatis pada line chart untuk data yang hilang |
| ✅ Selesai | Hourly count & average summary untuk monitoring kualitas data |
| 🔄 Rencana | REST API publik dengan autentikasi API key untuk integrasi sistem eksternal |
| 🔄 Rencana | Dashboard analytics PROPER (Program Penilaian Peringkat Kinerja Perusahaan) |
| 🔄 Rencana | Mobile-responsive PWA (Progressive Web App) |
| 🔄 Rencana | Webhook outbound untuk integrasi SCADA / third-party systems |
| 🔄 Rencana | Multi-tenancy (isolasi data antar organisasi dalam satu instance) |
| 🔄 Rencana | Two-factor authentication (2FA) |
| 🔄 Rencana | Audit trail lengkap untuk kepatuhan ISO 14001 |

---

## License

Didistribusikan di bawah lisensi **MIT**. Lihat file [`LICENSE`](LICENSE) untuk informasi selengkapnya.

---

## Contact

**Project Maintainer**

Untuk pertanyaan teknis, laporan bug, atau kontribusi, silakan buka *issue* di repositori GitHub atau hubungi melalui:

- **GitHub Issues:** [github.com/devhas-cloud/data-center/issues](https://github.com/devhas-cloud/data-center/issues)
- **Email:** maintainer@example.com

---

<p align="center">Built with ❤️ using <a href="https://laravel.com">Laravel</a></p>
