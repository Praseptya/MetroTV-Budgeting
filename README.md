# 💼 MetroTV Budgeting System

![Laravel](https://img.shields.io/badge/Laravel-11.x-red?logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-Database-orange?logo=mysql)
![Chart.js](https://img.shields.io/badge/Chart.js-3.9.1-purple?logo=chartdotjs)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.0-indigo?logo=bootstrap)

> **MetroTV Budgeting** adalah aplikasi berbasis web untuk mengelola dan memantau pengajuan anggaran di lingkungan MetroTV.  
> Sistem ini mempermudah proses *template creation*, *budget submission*, dan *approval workflow* agar lebih cepat, akurat, dan transparan.

---

## 🧭 Fitur Utama

### 🔹 Dashboard
- Statistik pengajuan anggaran berdasarkan periode
- Filter cepat (12 bulan / 30 hari / 7 hari)
- Tabel ringkasan status dan grafik menggunakan **Chart.js**

### 🔹 Master Data
- **Master Item**: daftar item biaya lengkap dengan satuan & harga
- **Master Program**: pengelolaan data program & PIC
- **Master Template**: struktur template anggaran turunan dari program & item


### 🔹 Pengajuan & Approval
- Proses pengajuan anggaran dari template yang telah dibuat
- Workflow persetujuan berjenjang dengan status: *Draft → Submitted → Approved/Rejected*
- Log aktivitas & catatan komentar untuk setiap tahap

### 🔹 User Management
- Hak akses berdasarkan role (Admin, PIC, Approver)
- CRUD user via halaman *Management User*
- Flash message & validasi otomatis pada form

---

## ⚙️ Tech Stack

| Komponen | Teknologi |
|-----------|------------|
| Backend | Laravel 11.x |
| Frontend | Blade Template + Bootstrap 5 |
| Database | MySQL |
| Chart Visualization | Chart.js 3.9.1 |
| Authentication | Laravel Breeze / Auth manual |
| Deployment | Localhost (XAMPP) / Web Server PHP 8.2+ |

---

## 👨‍💻 Pengembang

Nama: Muhamad Arya Praseptya
Instansi: Universitas Pakuan – Fakultas MIPA
Project: Praktik Lapang – Sistem Pengelolaan Pengajuan Anggaran
GitHub: @Praseptya

---

## 📜 Lisensi

Aplikasi ini dibuat untuk kepentingan pembelajaran dan pengembangan internal.
Distribusi ulang tanpa izin tertulis dilarang.
