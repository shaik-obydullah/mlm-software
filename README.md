# MLM Software

A full-featured binary MLM (network marketing) platform rebuilt from a legacy CodeIgniter 2.x application into **Laravel 13** with modern architecture, API + Blade dashboard, and a tested business logic layer.

## Features

- **Binary MLM tree** — left/right leg placement, auto-balancing, materialized path tree, downline reports
- **Multi-level commission engine** — sponsor bonus (10%), matching bonus on weaker leg, 7-level commissions, royalty placeholder
- **CV system** — 7 purchase tiers, bank/wallet payments, PIN-based activation, CV history
- **PIN management** — generate, assign, validate, expire activation PINs
- **Wallet & withdrawals** — credit/debit/hold/release, 2% withdrawal fee, KYC-gated requests, admin approval
- **Monthly interest** — configurable rate, auto-crediting to active CV
- **KYC workflow** — document upload, admin review, multi-document approval
- **Roles & permissions** — Spatie permissions (admin / agent / member)
- **Support tickets** — member submission, admin replies, status management
- **Reports & exports** — earnings, downline, commissions, withdrawals (CSV / PDF)
- **Audit trail** — every critical action is logged with subject, before/after values
- **Dockerized** — PHP-FPM + Nginx + MySQL + Redis via Docker Compose

## Tech Stack

| Layer      | Technology                                        |
|------------|---------------------------------------------------|
| Backend    | Laravel 13, PHP 8.3                                |
| Frontend   | Blade, Alpine.js, Tailwind CSS 4                   |
| Database   | MySQL 8.0 (SQLite in tests)                        |
| Auth       | Laravel Sanctum + Spatie Permissions               |
| Queue      | Database driver (Redis available via Compose)      |
| Exports    | DomPDF, CSV streaming                              |
| Testing    | PHPUnit 12 (127 tests, 355 assertions)             |
| CI         | GitHub Actions (Pint + PHPUnit on SQLite & MySQL)  |
| Containers | Docker + Docker Compose                            |

## Installation

### Local

```bash
git clone https://github.com/shaik-obydullah/mlm-software.git
cd mlm-software

composer install
cp .env.example .env
php artisan key:generate

npm install --ignore-scripts
npm run build

# SQLite (quick start)
php artisan migrate --seed

# or MySQL (edit .env first)
php artisan migrate --seed

php artisan serve
```

### Docker

```bash
cp .env.example .env
# set APP_KEY (php artisan key:generate), and keep DB defaults from docker-compose.yml

docker compose up -d --build
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan db:seed --class=DemoDataSeeder
```

The app runs on `http://localhost:80`. Services: MySQL `localhost:3307`, Redis `localhost:6380`.

### Demo Data & Credentials

`php artisan db:seed --class=DemoDataSeeder` creates an admin, a root sponsor and 30 members with activated CVs and commissions.

| Role   | Email              | Password   |
|--------|--------------------|------------|
| Admin  | `admin@mlm.local`  | `password` |
| Root   | `root@mlm.local`   | `password` |

## Tests

```bash
composer test            # or: php artisan test
vendor/bin/pint --test   # code style
```

The suite runs against in-memory SQLite by default (see `phpunit.xml`). CI additionally runs the feature suite against MySQL 8.

## API Overview

All endpoints are versioned under `/api/v1` and use Bearer token authentication (Sanctum).

```
POST  /api/v1/register              Register a member (optional sponsor_unique_id)
POST  /api/v1/login                 Login and receive a token
POST  /api/v1/logout                Revoke current token
GET   /api/v1/user                  Authenticated profile

GET   /api/v1/users                 List users (permission: view users)
GET   /api/v1/users/{id}            User detail

GET   /api/v1/mlm/tree/{userId}     Binary tree (depth param)
GET   /api/v1/mlm/downline/{userId} Downline + leg volumes
POST  /api/v1/mlm/place             Manual placement (admin)

GET   /api/v1/cv/tiers              Active CV tiers
POST  /api/v1/cv/purchase           Create purchase (bank|wallet)
POST  /api/v1/cv/{id}/pay           Mark paid (permission: approve payments)
POST  /api/v1/cv/{id}/activate      Activate (optional pin_code)

POST  /api/v1/pins/generate         Generate PINs (permission: manage pins)
POST  /api/v1/pins/assign           Assign PIN to member
POST  /api/v1/pins/use              Validate a PIN

GET   /api/v1/commissions/rules     Commission rules
POST  /api/v1/commissions/process   Process commissions (permission)

GET   /api/v1/wallet/balance        Balance summary
GET   /api/v1/wallet/transactions   Filter by category/type
POST  /api/v1/withdrawal/request    Request withdrawal (KYC required)
POST  /api/v1/withdrawal/{id}/approve  Approve (permission)

POST  /api/v1/kyc/upload            Upload KYC document
GET   /api/v1/kyc/documents         Own documents (or all for admin)
POST  /api/v1/kyc/{id}/review       Review (permission: review kyc)

GET   /api/v1/reports/earnings      Earnings summary (+platform totals for admin)
GET   /api/v1/reports/downline      Downline statistics
GET   /api/v1/reports/export/{type} Export CSV (commissions|transactions|withdrawals|downline)

GET/POST/PATCH /api/v1/tickets      Support tickets + replies
```

## Commission Model

| Type              | Calculation                                |
|-------------------|--------------------------------------------|
| Sponsor bonus     | 10% of the referred member's CV amount     |
| Matching bonus    | 5% of the weaker binary leg's volume       |
| Level commission  | 5/4/3/2/2/2/2% across 7 upline levels      |
| Royalty bonus     | Placeholder — scheduled pool distribution  |

## Project Layout

```
app/
├── Enums/          Domain status enums
├── Http/Controllers/{Api,Web}/   JSON API + Blade controllers
├── Models/         Eloquent models (22 tables)
├── Services/       Business logic: MlmTree, Cv, Pin, Commission, Wallet, Interest, Audit
├── Jobs/           CalculateCommissions, GeneratePinBatch, ProcessInterestAccrual
├── helpers.php     money() helper
database/
├── migrations/     24 migrations
├── seeders/        Role, CvTier, CommissionRule, SystemConfig, DemoData
├── factories/      User, Pin, CvTier, CvPurchase, Commission, CvActivation
resources/views/    Blade + Tailwind 4 + Alpine.js dashboard
tests/              127 PHPUnit tests (services + API)
docker/             nginx.conf, supervisord.conf, php.ini
```

## License

MIT
