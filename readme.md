<div align="center">
  <h1>🎟️ Voucher & Loyalty Management System</h1>
  <p><strong>Ubah Pelanggan Biasa Menjadi Pelanggan Setia!</strong></p>
  <p>
    Aplikasi loyalitas pelanggan dan manajemen voucher modern yang dirancang untuk bisnis multi-cabang. 
    Kumpulkan *stamp*, tukarkan *promo*, dan pantau performa bisnis Anda dalam satu *Command Center*!
  </p>
</div>

---

## 🎯 Apa itu Aplikasi Ini?

Aplikasi ini adalah solusi "All-in-One" untuk mengelola program loyalitas (*Loyalty Program*) bisnis Anda. Baik Anda memiliki kedai kopi, *barbershop*, atau toko ritel, aplikasi ini menggantikan kartu *stamp* kertas tradisional menjadi sistem digital yang aman, canggih, dan anti-kecurangan.

## ✨ Mengapa Harus Pakai Aplikasi Ini?

- 🚀 **Meningkatkan Retensi Pelanggan**: Buat pelanggan terus kembali dengan iming-iming promo menarik.
- 📱 **Digital & Modern**: Selamat tinggal kartu kertas yang gampang hilang. Semua data tersimpan aman di sistem.
- 🏢 **Multi-Cabang (Outlet)**: Punya 5 cabang? Tidak masalah! Satu akun pelanggan berlaku di semua cabang Anda.
- 🛡️ **Anti-Kecurangan (Smart Validation)**: Dilengkapi kecerdasan buatan sederhana untuk mencegah kasir (*capster*) memasukkan jumlah poin yang tidak wajar.
- 📊 **Pantauan Instan**: Lihat siapa pelanggan tersultan dan cabang mana yang paling ramai langsung dari *Dashboard*.

---

## 🚀 Fitur Unggulan

### 👥 Manajemen Pelanggan Terpusat
Satu data pelanggan terhubung ke seluruh jaringan *outlet* Anda. Lacak riwayat kedatangan dan total poin mereka dengan mudah.

### 🎁 Earn & Redeem
- **Earn**: Berikan poin / *stamp* berdasarkan nominal belanja pelanggan secara dinamis.
- **Redeem**: Pelanggan menukarkan poin mereka dengan berbagai pilihan promo cantik yang sudah Anda atur.

### 💬 Integrasi WhatsApp (RuangWA)
Kirimkan notifikasi WhatsApp otomatis ke pelanggan saat mereka mendapatkan poin baru atau sukses menukarkan promo. Pendekatan personal yang sangat disenangi pelanggan!

### ⚙️ Fleksibilitas "White-Label"
Ganti nama aplikasi, logo, dan sebutan mata uang virtual Anda (*Stamp*, *Poin*, *Koin*, dll) langsung dari halaman Pengaturan tanpa perlu menyentuh kode.

---

## 💻 Tech Stack (Untuk Developer)

Aplikasi ini dibangun dengan mengedepankan performa, keamanan, dan kesederhanaan *deployment*:
- **Backend**: Native PHP 8.1+ (Super ringan, tanpa *framework* yang berat)
- **Database**: MySQL / MariaDB (Performa tinggi)
- **Frontend**: HTML5, CSS3 Custom (Grid & Flexbox modern), Vanilla JS
- **Arsitektur**: Menggunakan konsep *Singleton* untuk koneksi DB dan sistem *Middleware* mandiri (Auth & CSRF Protection).

---

## 🛠️ Instalasi Cepat

Ingin mencoba menjalankannya di komputer lokal Anda? Ikuti langkah mudah berikut:

1. **Clone Repository**
   ```bash
   git clone https://github.com/arielthekillers/voucher-app.git
   cd voucher-app
   ```

2. **Install Dependensi**
   Pastikan Anda sudah menginstal [Composer](https://getcomposer.org/).
   ```bash
   composer install
   ```

3. **Konfigurasi Database**
   Copy file `.env.example` menjadi `.env`. Buka file `.env` lalu sesuaikan koneksi database Anda:
   ```ini
   DB_HOST=localhost
   DB_NAME=voucher
   DB_USER=root
   DB_PASS=
   ```

4. **Jalankan Aplikasi**
   Gunakan server PHP bawaan atau jalankan di XAMPP/Laragon.
   ```bash
   php -S localhost:8000 -t public
   ```
   Buka browser dan akses: `http://localhost:8000/admin/login.php`

> **Akun Default Administrator:**
> - **Username**: `admin`
> - **Password**: `admin123`
> *(Harap segera diganti saat aplikasi online!)*

---

## 👨‍💻 Kreator

Dibuat dengan ❤️ oleh **arielthekillers**.
Temukan proyek-proyek seru lainnya di [GitHub Profile](https://github.com/arielthekillers).
