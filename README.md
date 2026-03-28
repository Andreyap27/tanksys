# TankSys Pro

Sistem manajemen bisnis bahan bakar minyak (BBM) berbasis web untuk **PT. Anugrah Energi Petrolum**. Dibangun dengan Laravel 12 dan Vite, mencakup modul purchase, sales, stock, expenses, laporan keuangan, dan mobil tangki.

---

## Fitur Utama

- **Dashboard** — Ringkasan penjualan, pembelian, profit/loss, stok, dan pengeluaran bulan ini dengan grafik 6 bulan terakhir
- **Purchase** — Pencatatan pembelian BBM dari vendor
- **Stock** — Manajemen saldo stok BBM secara otomatis
- **Sales** — Penjualan BBM ke customer beserta cetak invoice
- **Capital** — Pencatatan modal usaha
- **Expenses** — Pencatatan pengeluaran operasional per kategori
- **Mobil Tangki** — Pencatatan pendapatan pengiriman
- **Laporan** — Total Purchase, Total Sale, Total Expense, Profit/Loss, Total Mobil Tangki dengan filter tahun dan cetak
- **User Management** — Manajemen akun pengguna sistem
- **Customer & Vendor** — Master data customer dan vendor

---

## Requirement

|Komponen|Versi Minimum|
|--------|-------------|
|PHP|8.2|
|Laravel|12.x|
|Node.js|18.x|
|NPM|9.x|
|Database|MySQL / SQLite|
|Web Server|Apache / Nginx / XAMPP|

---

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/your-repo/tanksys.git
cd tanksys
```

### 2. Install Dependency PHP

```bash
composer install
```

### 3. Install Dependency Node

```bash
npm install
```

### 4. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env` dan sesuaikan konfigurasi database:

```env
APP_NAME="TankSys Pro"
APP_URL=http://localhost/tanksys/public

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tanksys
DB_USERNAME=root
DB_PASSWORD=
```

> Jika menggunakan SQLite, biarkan `DB_CONNECTION=sqlite` dan hapus baris DB lainnya. File database akan dibuat otomatis di `database/database.sqlite`.

### 5. Jalankan Migrasi & Seeder

```bash
php artisan migrate
php artisan db:seed
```

Seeder akan membuat akun admin default:

|Field|Value|
|-----|-----|
|Username|`admin`|
|Password|`password`|

### 6. Build Assets

**Mode Development (dengan HMR):**

```bash
npm run dev
```

**Mode Production:**

```bash
npm run build
```

### 7. Jalankan Aplikasi

```bash
php artisan serve
```

Akses di browser: [http://localhost:8000](http://localhost:8000)

---

## Instalasi dengan XAMPP

1. Copy folder project ke `C:\xampp\htdocs\tanksys`
2. Buat database baru di phpMyAdmin dengan nama `tanksys`
3. Atur `.env` dengan `DB_CONNECTION=mysql` dan sesuaikan kredensial
4. Buka terminal di folder project, jalankan:

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run build
```

1. Akses di browser: [http://localhost/tanksys/public](http://localhost/tanksys/public)

---

## Struktur Modul

```text
app/
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── PurchaseController.php
│   ├── SaleController.php
│   ├── StockController.php
│   ├── ExpenseController.php
│   ├── LoriController.php
│   ├── ReportController.php
│   ├── CustomerController.php
│   ├── VendorController.php
│   └── UserController.php
├── Models/
│   ├── Purchase.php
│   ├── Sale.php
│   ├── Stock.php
│   ├── Expense.php
│   ├── Lori.php
│   ├── Customer.php
│   └── Vendor.php
resources/
├── views/
│   ├── layouts/         # App layout, guest layout, partials
│   ├── dashboard/
│   ├── purchase/
│   ├── sales/
│   ├── stock/
│   ├── expenses/
│   ├── lori/
│   ├── report/
│   └── errors/          # Halaman error 404 & 500
└── css/
    └── app.css          # Single source of truth untuk semua style
```

---

## Teknologi

- **Backend** — Laravel 12, PHP 8.2
- **Frontend** — Vite, Vanilla CSS (custom design system)
- **Icons** — Lucide Icons
- **Charts** — Chart.js
- **Tables** — DataTables (jQuery)
- **Font** — Nunito (Google Fonts)

---

## Lisensi

Aplikasi ini dikembangkan khusus untuk internal **PT. Anugrah Energi Petrolum**.
Seluruh hak cipta dilindungi &copy; 2026 — Developed by [AIKU TEAM](https://aikupos.com/)
