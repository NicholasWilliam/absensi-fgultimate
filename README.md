# Sistem Absensi Rental PS (Versi Laravel)

Versi Laravel dari prototype absensi karyawan: login (username/password), absen pakai kamera langsung
(bukan pilih dari galeri) + verifikasi GPS geofencing, panel admin buat log absen, jadwal shift, kelola
karyawan, pengaturan lokasi, dan **laporan mingguan/bulanan** (rekap + export CSV).

Stack: **Laravel 13, Eloquent, Blade, MySQL, Tailwind CSS (CDN), Alpine.js (CDN)**.
Sengaja pakai Tailwind/Alpine lewat CDN (bukan Vite build) supaya kamu gak perlu `npm install` sama sekali —
cukup `composer install` aja.

> ⚠️ **Catatan jujur:** project ini aku susun manual dari skeleton resmi Laravel (aku ambil dari GitHub,
> bukan lewat `composer create-project`), karena environment aku gak punya akses ke Packagist buat narik
> dependency Laravel. Jadi semua kode (migration, model, controller, routes, Blade view) sudah aku tulis
> dan cek sintaksnya satu per satu (`php -l` semua lolos, termasuk cek manual keseimbangan tag Blade), tapi
> **aku belum bisa jalanin `composer install` beneran** buat testing end-to-end di sisi aku. Begitu kamu
> `composer install` di komputer kamu (yang ada akses internet), kalau nemu error, kirim aja pesan errornya
> ke aku, nanti langsung aku benerin.

## Cara Menjalankan (Local)

### 1. Install dependency
```
composer install
```

### 2. Setup environment
```
cp .env.example .env
php artisan key:generate
```
Edit `.env` kalau kredensial MySQL kamu beda dari default (`root` / password kosong). Defaultnya sudah
diset pakai database bernama `ps_absensi`.

### 3. Buat database & migrasi
Buat database MySQL kosong bernama `ps_absensi` (lewat phpMyAdmin/CLI), lalu:
```
php artisan migrate --seed
```
Perintah ini otomatis membuat semua tabel + akun contoh (admin & karyawan) + setting lokasi default.

### 4. Buat symlink storage (buat nampilin foto absen)
```
php artisan storage:link
```

### 5. Jalankan server
```
php artisan serve
```
Buka `http://localhost:8000` di browser.

### 6. Kamera & GPS butuh HTTPS (kecuali localhost)
Sama seperti versi sebelumnya — browser modern cuma izinkan akses kamera & GPS di `localhost` atau HTTPS.
Testing lokal aman-aman saja; begitu deploy ke domain publik, pastikan pakai SSL.

## Akun Contoh (dari seeder)

| Role     | Username   | Password     |
|----------|------------|--------------|
| Admin    | admin      | admin123     |
| Karyawan | karyawan1  | karyawan123  |

## Sebelum Dipakai Beneran

1. Ganti koordinat lokasi rental di **Admin → Pengaturan Lokasi** (default masih dummy).
2. Ganti password default akun admin & karyawan contoh.
3. Tambah karyawan asli lewat **Admin → Karyawan**.
4. Input jadwal shift lewat **Admin → Jadwal Shift**.

## Fitur

- **Login** berbasis session Laravel (`Auth::attempt`), dengan role `admin` / `karyawan`.
- **Absen karyawan**: kamera langsung via `getUserMedia()` (gak bisa pilih dari galeri), watermark
  timestamp otomatis di foto, GPS wajib diisi sebelum submit.
- **Geofencing**: jarak dihitung pakai formula Haversine, dibandingkan ke radius toleransi yang admin set.
- **Deteksi telat otomatis**: dicocokkan ke jadwal shift hari itu (toleransi 10 menit, bisa diubah di
  `app/Http/Controllers/CheckinController.php`).
- **Proteksi double-submit**: gak bisa absen masuk/pulang dua kali di hari yang sama.
- **Dashboard admin**: log absen harian + foto + status + jarak GPS, plus alert karyawan yang
  punya jadwal tapi belum check-in.
- **Laporan mingguan/bulanan** (`Admin → Laporan`): rekap per karyawan (total jadwal, masuk, tepat waktu,
  telat, tidak hadir) untuk periode Minggu Ini / Minggu Lalu / Bulan Ini / rentang custom, plus log lengkap
  periode tersebut dan **export ke CSV**.
- **Kelola karyawan** & **pengaturan lokasi geofence** dari panel admin.

## Struktur Folder (yang relevan)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── CheckinController.php        # Logic absen: foto, GPS, status telat
│   │   └── Admin/
│   │       ├── DashboardController.php  # Log harian
│   │       ├── ReportController.php     # Laporan mingguan/bulanan + export CSV
│   │       ├── ScheduleController.php   # Jadwal shift
│   │       ├── EmployeeController.php   # Kelola karyawan
│   │       └── SettingController.php    # Setting lokasi geofence
│   └── Middleware/EnsureUserHasRole.php # Proteksi role admin/karyawan
├── Models/
│   ├── User.php, Shift.php, Attendance.php, Setting.php
database/
├── migrations/    # users (username+role+is_active), shifts, attendances, settings
├── seeders/DatabaseSeeder.php
resources/views/
├── layouts/app.blade.php, layouts/admin.blade.php
├── auth/login.blade.php
├── checkin.blade.php
└── admin/dashboard.blade.php, report.blade.php, schedule.blade.php, employees.blade.php, settings.blade.php
routes/web.php
```
