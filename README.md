# 🏟️ Booking Lapangan API

Booking Lapangan API adalah **Web Service berbasis REST API** yang dikembangkan menggunakan **Laravel 12** sebagai bagian dari **Tugas UAS Mata Kuliah Pengembangan Aplikasi Bisnis (PAB)**.

API ini digunakan untuk mengelola **pemesanan lapangan olahraga** oleh sistem mitra (partner) secara **Machine to Machine (M2M)** dengan keamanan **OAuth2 menggunakan Laravel Passport (client_credentials)**.

---

## 🎯 Tujuan Sistem

Sistem ini bertujuan untuk:
- Menyediakan informasi ketersediaan lapangan olahraga
- Memfasilitasi proses booking lapangan oleh sistem mitra
- Menangani konfirmasi pembayaran (simulasi webhook)
- Menandai booking yang sudah digunakan di lapangan

---

## 🧩 Studi Kasus

Sebuah pengelola lapangan olahraga memiliki beberapa jenis lapangan, seperti:
- Lapangan Voli  
- Lapangan Badminton  
- Lapangan Futsal  
- Lapangan Basket  

Setiap lapangan:
- Memiliki **harga berbeda**
- Memiliki **kuota maksimal booking per hari**
- Dapat dipesan oleh sistem partner melalui API

---

## 🔐 Keamanan API

API menggunakan **Laravel Passport** dengan skema:
- OAuth2
- Grant Type: **client_credentials**
- Authorization: `Bearer Token`

Semua endpoint utama **dilindungi autentikasi**, kecuali endpoint webhook.

---

## 🛠️ Teknologi yang Digunakan

- Laravel 12
- Laravel Passport (OAuth2)
- MySQL
- Swagger (L5-Swagger)
- REST API
- (Opsional) Xendit / simulasi webhook pembayaran

---

## 📚 Struktur Data Utama

### Field (Lapangan)
- id
- name
- type
- price

### Field Schedule
- field_id
- schedule_date
- booked
- used

### Booking
- customer_name
- email
- field_schedule_id
- is_booked
- is_used
- unique_code

---

## 🚀 Daftar Endpoint (Sesuai Ketentuan UAS)

### 1️⃣ GET /api/partners/fields
**Deskripsi:**  
Mengambil daftar lapangan beserta ketersediaan (sisa kuota) per tanggal.

**Akses:**  
Partner (M2M) – membutuhkan access token

---

### 2️⃣ POST /api/partners/fields
**Deskripsi:**  
Membuat booking lapangan berdasarkan `field_id` dan tanggal booking.  
Harga otomatis diambil dari tabel lapangan.

**Akses:**  
Partner (M2M) – membutuhkan access token

---

### 3️⃣ POST /api/partners/fields/book
**Deskripsi:**  
Endpoint webhook untuk menerima notifikasi pembayaran dan mengonfirmasi booking.

**Akses:**  
Tanpa autentikasi (simulasi webhook sistem pembayaran)

---

### 4️⃣ POST /api/bookings/{id}
**Deskripsi:**  
Menandai booking sudah digunakan di lapangan (scan / validasi).

**Akses:**  
Petugas lapangan / sistem internal (H2H)

---

## 🧪 Cara Testing API

### 1️⃣ Generate Token
Gunakan Passport client_credentials untuk mendapatkan access token.

### 2️⃣ Test Endpoint
API dapat diuji menggunakan:
- Swagger UI
- Postman
- cURL









