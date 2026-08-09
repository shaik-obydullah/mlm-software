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

## Core Features

### 1. User Management

- Admin, Agent, Member roles
- Registration with sponsor/referral
- Profile management with nominee info
- KYC document upload
- Account verification workflow

### 2. MLM Tree System

- Binary tree structure (Left/Right leg)
- Sponsor tracking
- Downline visualization
- Tree depth levels
- Auto-placement algorithms

### 3. CV (Cash Value) System

- Multiple CV tiers (200, 400, 800, 1500, 2500, 3000, 4000)
- CV purchase with payment
- CV activation status
- CV history tracking

### 4. PIN System

- Generate activation PINs
- PIN assignment to users
- PIN usage tracking
- PIN expiration

### 5. Commission & Bonus System

- Sponsor bonus (direct referral)
- Matching bonus (binary tree)
- Level commissions (multi-level)
- Royalty bonuses
- Auto-calculation engine

### 6. Fund Management

- Wallet balance
- Fund requests (withdrawal)
- Bank transfer details
- Transaction fees
- Withdrawal history

### 7. Interest System

- Monthly interest on active CV
- Interest rate configuration
- Auto-credit to wallet
- Interest history

### 8. Support System

- Ticket creation
- Admin response
- Ticket status tracking
- Priority levels

### 9. Dashboard & Reports

- Real-time earnings
- Downline tree view
- Commission reports
- Withdrawal reports
- Monthly statements

---

## Database Schema

### Core Tables (22 tables)

```sql
-- Users & Auth
users (id, sponsor_id, unique_id, name, email, password, phone,
       role, status, balance, created_at, timestamps)

roles (id, name, guard_name)
model_has_roles (role_id, model_id)

-- KYC Documents
kyc_documents (id, user_id, type, file_path, status, reviewed_by, timestamps)

-- MLM Structure
mlm_trees (id, user_id, parent_id, position, level, path, timestamps)
sponsors (id, sponsor_id, sponsee_id, type, timestamps)

-- CV System
cv_tiers (id, name, amount, description, status, timestamps)
cv_purchases (id, user_id, tier_id, amount, payment_method,
              status, transaction_id, timestamps)
cv_activations (id, purchase_id, user_id, activation_date,
                expiry_date, status, timestamps)

-- PIN System
pins (id, code, cv_tier_id, created_by, assigned_to,
      status, expires_at, used_at, timestamps)

-- Commission System
commission_rules (id, name, type, rate, min_volume,
                  max_level, status, timestamps)
commissions (id, user_id, from_user_id, type, amount,
             status, paid_at, timestamps)
commission_batches (id, period_start, period_end, total_amount,
                    status, processed_by, timestamps)

-- Fund Management
wallets (id, user_id, balance, locked_amount, timestamps)
transactions (id, wallet_id, type, amount, reference,
              status, timestamps)
withdrawal_requests (id, user_id, amount, fee, net_amount,
                     method, bank_name, account_number,
                     status, approved_by, timestamps)

-- Interest System
interest_rates (id, rate, effective_date, status, timestamps)
interest_accruals (id, user_id, cv_purchase_id, amount,
                   rate, period, status, timestamps)

-- Support
tickets (id, user_id, subject, priority, status, timestamps)
ticket_replies (id, ticket_id, user_id, message, timestamps)

-- Settings
settings (id, key, value, group, timestamps)

-- Audit
audit_logs (id, user_id, action, subject_type, subject_id,
            old_values, new_values, timestamps)
```

---

## Commission Structure

### Types

| Type             | Description       | Calculation               |
| ---------------- | ----------------- | ------------------------- |
| Sponsor Bonus    | Direct referral   | 10% of CV amount          |
| Matching Bonus   | Binary tree match | 5% per matched pair       |
| Level Commission | Multi-level       | 2-5% per level (7 levels) |
| Royalty Bonus    | Top performers    | Monthly pool distribution |

### Calculation Engine

```
1. Sponsor Bonus
   - When sponsee activates CV
   - 10% of CV amount credited to sponsor

2. Matching Bonus
   - Binary tree: Left leg vs Right leg
   - Match when both legs have activation
   - 5% of smaller leg CV amount

3. Level Commission
   - 7 levels deep
   - Level 1: 5%, Level 2: 4%, Level 3: 3%
   - Level 4-7: 2% each
   - Based on downline CV purchases

4. Royalty Bonus
   - Monthly pool from company revenue
   - Distributed to top 100 performers
   - Based on total team volume
```

---

## API Endpoints

### Auth

```
POST   /api/login
POST   /api/register
POST   /api/logout
GET    /api/user
```

### Users

```
GET    /api/users
GET    /api/users/{id}
PUT    /api/users/{id}
GET    /api/users/{id}/downline
GET    /api/users/{id}/commissions
```

### MLM Tree

```
GET    /api/mlm/tree/{userId}
GET    /api/mlm/sponsor/{userId}
GET    /api/mlm/downline/{userId}
POST   /api/mlm/place
```

### CV System

```
GET    /api/cv/tiers
POST   /api/cv/purchase
GET    /api/cv/purchases/{userId}
GET    /api/cv/activations/{userId}
```

### PIN System

```
POST   /api/pins/generate
POST   /api/pins/assign
GET    /api/pins/available
POST   /api/pins/use
```

### Commissions

```
GET    /api/commissions/rules
GET    /api/commissions/calculate/{userId}
GET    /api/commissions/history/{userId}
POST   /api/commissions/process
```

### Wallet

```
GET    /api/wallet/balance
GET    /api/wallet/transactions
POST   /api/withdrawal/request
GET    /api/withdrawal/history
```

### Support

```
GET    /api/tickets
POST   /api/tickets
GET    /api/tickets/{id}
POST   /api/tickets/{id}/reply
```

### Reports

```
GET    /api/reports/earnings
GET    /api/reports/downline
GET    /api/reports/commissions
GET    /api/reports/export/{type}
```

---

## Project Structure

```
mlm-software/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── UserController.php
│   │   │   │   ├── MlmTreeController.php
│   │   │   │   ├── CvController.php
│   │   │   │   ├── PinController.php
│   │   │   │   ├── CommissionController.php
│   │   │   │   ├── WalletController.php
│   │   │   │   └── TicketController.php
│   │   │   └── Web/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Services/
│   │   ├── MlmTreeService.php
│   │   ├── CvService.php
│   │   ├── PinService.php
│   │   ├── CommissionService.php
│   │   ├── WalletService.php
│   │   └── InterestService.php
│   ├── Jobs/
│   │   ├── CalculateCommissions.php
│   │   ├── ProcessInterestAccrual.php
│   │   └── GeneratePinBatch.php
│   ├── Notifications/
│   └── Enums/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   └── views/
│       ├── layouts/
│       ├── dashboard/
│       ├── mlm/
│       ├── cv/
│       ├── wallet/
│       ├── tickets/
│       └── reports/
├── routes/
├── tests/
├── docker/
├── docker-compose.yml
├── README.md
└── .github/
    └── workflows/
        └── ci.yml
```

---

## Key Business Logic

### Binary Tree Placement

```
function placeInBinaryTree(userId, sponsorId):
    root = getSponsorTree(sponsorId)

    if root.left is null:
        place(root.left, userId)
    else if root.right is null:
        place(root.right, userId)
    else:
        // Recurse to next available position
        placeInBinaryTree(userId, getPreferredLeg(root))
```

### Matching Bonus Calculation

```
function calculateMatchingBonus(userId):
    leftVolume = getLeftLegVolume(userId)
    rightVolume = getRightLegVolume(userId)

    matchedVolume = min(leftVolume, rightVolume)
    bonus = matchedVolume * 0.05

    return bonus
```

### Commission Processing

```
function processCommissions(purchaseId):
    user = getUser(purchaseId)

    // 1. Sponsor Bonus
    sponsorBonus = purchase.amount * 0.10
    creditWallet(user.sponsor_id, sponsorBonus)

    // 2. Matching Bonus
    matchingBonus = calculateMatchingBonus(user)
    creditWallet(user.id, matchingBonus)

    // 3. Level Commissions (up to 7 levels)
    for level in 1..7:
        upline = getUpline(user, level)
        rate = getLevelRate(level)
        levelBonus = purchase.amount * rate
        creditWallet(upline.id, levelBonus)
```

---

## Tech Stack

| Layer      | Technology                                        |
| ---------- | ------------------------------------------------- |
| Backend    | Laravel 13, PHP 8.3                               |
| Frontend   | Blade, Alpine.js, Tailwind CSS 4                  |
| Database   | MySQL 8.0 (SQLite in tests)                       |
| Auth       | Laravel Sanctum + Spatie Permissions              |
| Queue      | Database driver (Redis available via Compose)     |
| Exports    | DomPDF, CSV streaming                             |
| Testing    | PHPUnit 12 (127 tests, 355 assertions)            |
| CI         | GitHub Actions (Pint + PHPUnit on SQLite & MySQL) |
| Containers | Docker + Docker Compose                           |

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

| Role  | Email             | Password   |
| ----- | ----------------- | ---------- |
| Admin | `admin@mlm.local` | `password` |
| Root  | `root@mlm.local`  | `password` |

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

| Type             | Calculation                               |
| ---------------- | ----------------------------------------- |
| Sponsor bonus    | 10% of the referred member's CV amount    |
| Matching bonus   | 5% of the weaker binary leg's volume      |
| Level commission | 5/4/3/2/2/2/2% across 7 upline levels     |
| Royalty bonus    | Placeholder — scheduled pool distribution |

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
