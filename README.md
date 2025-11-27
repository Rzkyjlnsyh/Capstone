# HPE System - Harga Perkiraan Estimasi

Sistem web untuk menghitung Harga Perkiraan Estimasi (HPE) berdasarkan riwayat pengadaan sebelumnya, dengan integrasi kurs JISDOR dari Bank Indonesia.

## Fitur Utama

### 1. Administrasi Produk & Komponen
- CRUD produk dengan kode unik
- Manajemen komponen penyusun (BoM - Bill of Materials)
- Flat BoM (1 level komponen)
- Pencarian dan filter produk

### 2. Riwayat Pengadaan
- Pencatatan transaksi pengadaan per komponen
- Penyimpanan harga dalam USD atau IDR
- Snapshot kurs saat transaksi
- Minimal 3 transaksi per komponen untuk perhitungan akurat

### 3. Integrasi Kurs JISDOR Bank Indonesia
- Auto-sync kurs USD→IDR harian dari BI API
- Manual sync via API endpoint
- Fallback mock data untuk development
- Scheduler harian (Senin-Jumat, 08:00)

### 4. Perhitungan HPE Otomatis
- Normalisasi harga berdasarkan kurs terbaru
- Rata-rata dari minimal 3 transaksi terakhir per komponen
- Agregasi ke level produk
- Margin/contingency configurable
- Warning untuk data tidak lengkap

### 5. Audit Trail
- Pencatatan semua perubahan data (create, update, delete)
- Log user, IP address, timestamp
- Filterable audit logs

### 6. Reporting & Export
- Export HPE results ke PDF/Excel
- Export produk & komponen ke PDF/Excel
- Filter berdasarkan tanggal, produk, dll

### 7. Dashboard & KPI
- Ringkasan total produk, komponen, HPE
- Kurs terbaru
- Hasil HPE terbaru

### 8. PWA Support
- Progressive Web App
- Offline support dengan service worker
- Installable di mobile/desktop

## Requirement

- PHP >= 8.2
- Composer
- MySQL/MariaDB atau SQLite
- Node.js & NPM (untuk asset compilation, opsional)

## Instalasi

1. Clone repository:
```bash
git clone <repository-url>
cd Capstone-Project-HPE/backend
```

2. Install dependencies:
```bash
composer install
```

3. Setup environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Konfigurasi database di `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hpe_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Migrate & seed database:
```bash
php artisan migrate:fresh --seed
```

6. Jalankan server:
```bash
php artisan serve
```

Aplikasi akan tersedia di `http://localhost:8000`

## Akun Default

Setelah seeding, tersedia 3 akun default:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@hpe.local | Admin#123 |
| Finance | finance@hpe.local | Finance#123 |
| Viewer | viewer@hpe.local | Viewer#123 |

## API Endpoints

### Authentication
- `POST /api/auth/login` - Login (email, password)
- `POST /api/auth/logout` - Logout (requires token)
- `GET /api/auth/me` - Get current user (requires token)

### Products
- `GET /api/products` - List produk (dengan filter search, status, category)
- `GET /api/products/{id}` - Detail produk + BoM
- `POST /api/products` - Create produk (admin only)
- `PUT/PATCH /api/products/{id}` - Update produk (admin only)
- `DELETE /api/products/{id}` - Delete produk (admin only)

### Components
- `GET /api/components` - List komponen
- `GET /api/components/{id}` - Detail komponen
- `POST /api/components` - Create komponen (admin only)
- `PUT/PATCH /api/components/{id}` - Update komponen (admin only)
- `DELETE /api/components/{id}` - Delete komponen (admin only)

### Product Components (BoM)
- `POST /api/products/{product}/components` - Tambah komponen ke produk (admin only)
- `PATCH /api/products/{product}/components/{productComponent}` - Update qty komponen (admin only)
- `DELETE /api/products/{product}/components/{productComponent}` - Hapus komponen dari produk (admin only)

### Purchase History
- `GET /api/purchase-histories` - List transaksi (dengan filter)
- `GET /api/purchase-histories/{id}` - Detail transaksi
- `POST /api/purchase-histories` - Create transaksi (admin/finance)
- `PUT/PATCH /api/purchase-histories/{id}` - Update transaksi (admin/finance)
- `DELETE /api/purchase-histories/{id}` - Delete transaksi (admin/finance)

### Exchange Rates
- `GET /api/exchange-rates` - List kurs (dengan filter tanggal)
- `GET /api/exchange-rates/latest` - Kurs terbaru USD/IDR
- `GET /api/exchange-rates/{id}` - Detail kurs
- `POST /api/exchange-rates/sync` - Sync kurs dari BI (admin only)

### HPE Calculation
- `POST /api/hpe/calculate` - Hitung HPE untuk produk (admin/finance)
- `GET /api/hpe/results` - List hasil HPE (dengan filter)
- `GET /api/hpe/results/{id}` - Detail hasil HPE

### Reporting
- `GET /api/reporting/export-hpe?type=pdf|excel&...` - Export HPE (admin/finance)
- `GET /api/reporting/export-products?type=pdf|excel` - Export produk (admin/finance)

### Dashboard
- `GET /api/dashboard` - KPI summary & recent HPE

### Audit Logs
- `GET /api/audit-logs` - List audit logs (admin only, dengan filter)
- `GET /api/audit-logs/{id}` - Detail audit log (admin only)

## Artisan Commands

### Sync Exchange Rates
```bash
# Sync untuk hari ini
php artisan rates:sync

# Sync untuk tanggal tertentu
php artisan rates:sync --date=2025-11-05

# Sync untuk range tanggal
php artisan rates:sync --range=2025-11-01:2025-11-05

# Gunakan mock data (untuk development)
php artisan rates:sync --mock
```

### Scheduler
Pastikan cron job berjalan untuk auto-sync harian:
```bash
* * * * * cd /path-to-project/backend && php artisan schedule:run >> /dev/null 2>&1
```

## Testing

```bash
# Run semua tests
php artisan test

# Run specific test
php artisan test --filter HpeCalculatorTest
```

## Struktur Project

```
backend/
├── app/
│   ├── Console/Commands/        # Artisan commands
│   ├── Http/
│   │   ├── Controllers/          # API controllers
│   │   └── Middleware/           # Auth, Role, Audit middleware
│   ├── Jobs/                     # Queue jobs (sync rates)
│   ├── Models/                   # Eloquent models
│   └── Services/                 # Business logic (HpeCalculator, BiExchangeRateService)
├── database/
│   ├── migrations/               # Database migrations
│   └── seeders/                  # Database seeders
├── public/
│   ├── manifest.json            # PWA manifest
│   ├── service-worker.js         # PWA service worker
│   └── offline.html             # Offline fallback page
├── resources/
│   └── views/
│       ├── layouts/              # Blade layouts
│       ├── pages/                # Blade pages
│       └── reports/              # PDF report templates
└── routes/
    ├── api.php                   # API routes
    └── web.php                   # Web routes
```

## Role & Permission

- **Admin**: Full access (CRUD semua data, sync rates, view audit logs)
- **Finance**: CRUD purchase history, calculate HPE, export reports
- **Viewer**: Read-only access (dashboard, products, components, HPE results)

## Demo Script (3-5 menit)

1. **Login** sebagai admin → `POST /api/auth/login`
2. **Dashboard** → `GET /api/dashboard` (lihat KPI)
3. **Lihat Produk** → `GET /api/products?with_components=1`
4. **Tambah Purchase History** → `POST /api/purchase-histories` (minimal 3 per komponen)
5. **Sync Kurs** → `POST /api/exchange-rates/sync`
6. **Hitung HPE** → `POST /api/hpe/calculate` dengan `product_id` dan `margin_percent`
7. **Lihat Hasil** → `GET /api/hpe/results/{id}`
8. **Export PDF** → `GET /api/reporting/export-hpe?type=pdf&hpe_result_id={id}`
9. **Audit Log** → `GET /api/audit-logs` (admin only)

## Troubleshooting

### BI API tidak bisa diakses
- Gunakan `--mock` flag saat sync rates
- Atau set `APP_ENV=local` di `.env` untuk auto-mock

### Service worker tidak register
- Pastikan aplikasi diakses via HTTPS atau localhost
- Cek browser console untuk error

### Test gagal
- Pastikan database sudah di-migrate & seed
- Run `php artisan migrate:fresh --seed` sebelum test

## License

Proprietary - Capstone Project HPE
