# Product Requirements Document (PRD)

## HRIS Attendance Management System

Version: 1.0

Status: Draft

---

# 1. Latar Belakang

Banyak perusahaan masih menggunakan proses absensi yang dilakukan secara manual atau menggunakan beberapa aplikasi yang terpisah untuk mengelola kehadiran, cuti, izin, dan lembur. Hal tersebut menyebabkan proses administrasi menjadi kurang efisien, sulit dipantau secara real-time, serta menyulitkan proses pelaporan.

HRIS Attendance Management System dikembangkan sebagai aplikasi berbasis web yang membantu perusahaan dalam mengelola data karyawan, absensi, cuti, izin, lembur, jadwal kerja, dan laporan secara terintegrasi.

---

# 2. Tujuan

Membangun sistem HRIS yang mampu:

* Mengelola data karyawan secara terpusat.
* Mempermudah proses absensi harian.
* Mengelola pengajuan izin, cuti, dan lembur.
* Membantu HR melakukan monitoring kehadiran.
* Menyediakan laporan absensi yang akurat.
* Mengurangi proses administrasi manual.

---

# 3. Ruang Lingkup

Versi pertama sistem mencakup:

* Authentication
* Dashboard
* Manajemen Karyawan
* Divisi
* Jabatan
* Shift Kerja
* Kalender Kerja
* Absensi
* Izin
* Cuti
* Lembur
* Pengumuman
* Laporan
* Profil Pengguna
* Pengaturan Sistem

Tidak termasuk:

* Payroll
* Rekrutmen
* Performance Appraisal
* Asset Management
* Fingerprint Device Integration
* Face Recognition
* Mobile Application

---

# 4. Target Pengguna

## Admin (HR)

Bertanggung jawab mengelola seluruh data sistem.

Hak akses:

* Kelola karyawan
* Kelola divisi
* Kelola jabatan
* Kelola shift
* Kelola hari libur
* Approval izin
* Approval cuti
* Approval lembur
* Melihat seluruh absensi
* Export laporan
* Pengaturan sistem

---

## Karyawan

Menggunakan sistem untuk aktivitas harian.

Hak akses:

* Check In
* Check Out
* Melihat riwayat absensi
* Mengajukan izin
* Mengajukan cuti
* Mengajukan lembur
* Melihat jadwal kerja
* Membaca pengumuman
* Mengubah profil

---

# 5. User Journey

## Admin

Login

↓

Dashboard

↓

Kelola Master Data

↓

Monitoring Absensi

↓

Approval Pengajuan

↓

Laporan

---

## Karyawan

Login

↓

Dashboard

↓

Check In

↓

Bekerja

↓

Check Out

↓

Riwayat Absensi

---

# 6. Functional Requirements

## 6.1 Authentication

### Admin

* Login
* Logout
* Ubah Password

### Karyawan

* Login
* Logout
* Ubah Password

---

## 6.2 Dashboard

### Admin

Menampilkan:

* Total Karyawan
* Hadir Hari Ini
* Terlambat
* Alpha
* Izin
* Cuti
* Lembur Hari Ini
* Pengajuan Pending
* Grafik Kehadiran
* Grafik Lembur
* Pengumuman

### Karyawan

Menampilkan:

* Status Kehadiran Hari Ini
* Jam Masuk
* Jam Pulang
* Total Jam Kerja
* Jadwal Hari Ini
* Sisa Cuti
* Riwayat Terakhir
* Pengumuman

---

## 6.3 Manajemen Karyawan

Admin dapat:

* Menambah karyawan
* Mengubah data
* Menghapus data
* Menonaktifkan akun
* Reset password
* Import data
* Export data

Data karyawan:

* NIK
* Nama
* Email
* Nomor HP
* Alamat
* Tanggal Lahir
* Jenis Kelamin
* Divisi
* Jabatan
* Shift
* Status
* Tanggal Bergabung
* Foto

---

## 6.4 Divisi

* Tambah divisi
* Edit divisi
* Hapus divisi
* Daftar anggota divisi

---

## 6.5 Jabatan

* Tambah jabatan
* Edit jabatan
* Hapus jabatan
* Level jabatan

---

## 6.6 Shift Kerja

Setiap shift memiliki:

* Nama shift
* Jam masuk
* Jam pulang
* Toleransi keterlambatan
* Jam istirahat

---

## 6.7 Kalender Kerja

Admin dapat mengelola:

* Hari kerja
* Hari libur nasional
* Libur perusahaan
* Cuti bersama

---

## 6.8 Absensi

### Check In

Menyimpan:

* Waktu
* Lokasi GPS
* Foto selfie (opsional)
* Device
* IP Address

### Check Out

Menyimpan:

* Waktu pulang
* Lokasi
* Foto selfie (opsional)
* Total jam kerja

Status absensi:

* Hadir
* Terlambat
* Alpha
* Izin
* Cuti
* Sakit
* WFH
* Dinas

Validasi:

* Radius kantor (Geofencing)
* Tidak dapat melakukan Check In lebih dari satu kali per hari
* Check Out hanya dapat dilakukan setelah Check In

---

## 6.9 Izin

Karyawan dapat:

* Mengajukan izin
* Mengunggah lampiran
* Membatalkan pengajuan sebelum diproses

Admin dapat:

* Menyetujui
* Menolak
* Memberikan catatan

Status:

* Pending
* Approved
* Rejected

---

## 6.10 Cuti

Karyawan dapat:

* Mengajukan cuti
* Melihat sisa cuti
* Melihat riwayat cuti

Admin dapat:

* Menyetujui
* Menolak
* Mengatur kuota cuti

---

## 6.11 Lembur

Karyawan dapat:

* Mengajukan lembur
* Melihat riwayat lembur

Admin dapat:

* Menyetujui
* Menolak
* Melihat rekap lembur

Data lembur:

* Tanggal
* Jam mulai
* Jam selesai
* Total durasi
* Alasan
* Status

---

## 6.12 Pengumuman

Admin:

* Membuat pengumuman
* Mengubah
* Menghapus
* Menjadwalkan publikasi

Karyawan:

* Melihat pengumuman

---

## 6.13 Laporan

Admin dapat menghasilkan laporan:

* Kehadiran
* Keterlambatan
* Cuti
* Izin
* Lembur

Filter:

* Periode
* Divisi
* Jabatan
* Karyawan

Export:

* Excel
* PDF

---

## 6.14 Profil

Karyawan dapat:

* Mengubah foto
* Mengubah nomor HP
* Mengubah alamat
* Mengubah password

---

## 6.15 Pengaturan Sistem

Admin dapat mengatur:

* Nama perusahaan
* Logo
* Lokasi kantor
* Radius absensi
* Jam kerja
* Zona waktu
* SMTP Email

---

# 7. Non-Functional Requirements

## Performance

* Dashboard dimuat kurang dari 3 detik.
* Proses Check In kurang dari 2 detik.
* Proses Check Out kurang dari 2 detik.

## Security

* Password disimpan menggunakan hashing.
* Authentication menggunakan JWT.
* Role Based Access Control (RBAC).
* HTTPS.
* Audit Log aktivitas penting.

## Reliability

* Backup database berkala.
* Validasi input pada seluruh form.
* Penanganan error yang informatif.

## Compatibility

* Google Chrome
* Microsoft Edge
* Mozilla Firefox
* Safari

Responsive untuk desktop, tablet, dan mobile.

---

# 8. Success Metrics

Sistem dianggap berhasil apabila:

* Seluruh proses absensi dilakukan melalui sistem.
* Waktu pembuatan laporan berkurang dibanding proses manual.
* Data absensi tersimpan secara akurat.
* Pengajuan izin, cuti, dan lembur dapat diproses secara digital.
* Admin dapat memonitor kehadiran secara real-time.

---

# 9. Future Enhancement

Versi berikutnya dapat menambahkan:

* Payroll
* Fingerprint Integration
* Face Recognition
* Mobile Application
* Push Notification
* QR Code Attendance
* Performance Appraisal
* Asset Management
* Rekrutmen
* Training Management
* Multi Company
* Multi Branch

## 10. Technology Stack

### Backend

* Laravel 12
* PHP 8.4+
* Laravel Sanctum (Authentication)
* Laravel Queue
* Laravel Scheduler

### Frontend

* Vue 3
* TypeScript
* Vite
* Vue Router
* Pinia (State Management)

### UI

* Tailwind CSS
* shadcn-vue
* Lucide Icons

### Database

* PostgreSQL

### Development Tools

* Composer
* Node.js
* npm/pnpm
* Git
* Docker (Opsional)

### Deployment

* Nginx
* PHP-FPM
* PostgreSQL
* Supervisor (Queue Worker)
* Linux Server (Ubuntu)

---

## Arsitektur

```text
Browser
    │
    ▼
Vue 3 + shadcn-vue + Tailwind CSS
    │
REST API
    │
    ▼
Laravel 12
    │
    ├── Authentication (Sanctum)
    ├── Business Logic
    ├── Validation
    ├── Queue
    └── Scheduler
    │
    ▼
PostgreSQL
```

---

## Alasan Pemilihan Teknologi

### Laravel

* Framework PHP yang matang dan stabil.
* Mendukung Authentication, Queue, Scheduler, Migration, Validation, dan ORM Eloquent.
* Memiliki ekosistem yang luas sehingga mudah dikembangkan.

### Vue 3

* Ringan dan cepat.
* Mudah dipadukan dengan Laravel.
* Cocok untuk membangun antarmuka yang interaktif.

### shadcn-vue

* Komponen UI modern dan konsisten.
* Mudah dikustomisasi.
* Dibangun di atas Tailwind CSS.

### Tailwind CSS

* Utility-first CSS framework.
* Mempercepat proses pengembangan antarmuka.
* Mendukung desain responsif dengan mudah.

### PostgreSQL

* Mendukung transaksi yang andal (ACID).
* Performa baik untuk data relasional.
* Sangat cocok untuk aplikasi HRIS yang membutuhkan integritas data tinggi.
