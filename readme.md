# Voucher App

Aplikasi voucher / poin / stamp **web-based, mobile-first**, dibuat menggunakan **PHP Native (tanpa framework)**.

Project ini ditujukan untuk kebutuhan multi-outlet dengan sistem:

- Login & role (Super Admin, Admin Outlet)
- Customer terpusat (1 customer bisa transaksi di outlet mana saja)
- Poin/coin/stamp
- Redeem promo
- Log transaksi yang immutable (audit friendly)

---

## 🚀 Fitur (Progressive)

### ✅ Authentication

- Login
- Logout
- Session-based auth
- Role-based access

### 🔄 Management (on going)

- User management (Super Admin)
- Outlet management
- Customer management
- Promo management
- Poin / coin assignment
- Redeem promo

### 📊 Dashboard

- Statistik ringkas
- Aktivitas terbaru

---

## 🗂️ Struktur Folder

```
voucher/
├── app/
│   ├── config/
│   ├── core/
│   ├── middleware/
│   └── modules/
│
├── public/
│   └── admin/
│       ├── login.php
│       ├── dashboard.php
│       └── logout.php
│
├── resources/
│   └── views/
│       └── layouts/
│           ├── header.php
│           ├── sidebar.php
│           └── footer.php
│
├── routes/
├── storage/
└── .gitignore
```

---

## ⚙️ Requirement

- PHP >= 8.1
- MySQL / MariaDB
- Apache (XAMPP / Laragon)

---

## 🔐 Default Super Admin (Seeder – lokal saja)

```txt
username: admin
password: admin123
```

> ⚠️ File seeder **tidak ikut ke GitHub** dan **harus dihapus setelah dipakai**

---

## 🧠 Catatan

- Project ini **tidak menggunakan framework**
- Fokus pada struktur rapi & scalable
- Mudah dikembangkan ke API / Mobile App di tahap selanjutnya

---

## 👨‍💻 Author

**Ariel The Killers**
GitHub: [https://github.com/arielthekillers](https://github.com/arielthekillers)

---

---
