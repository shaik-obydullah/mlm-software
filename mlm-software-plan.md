# MLM Software - Laravel 13 Rebuild Plan

## Overview

Rebuild of the old CodeIgniter MLM software (2017) using Laravel 13 with modern architecture, security, and features. The original system had 16 tables handling agents, members, CV system, PIN activation, downline management, and financial transactions.

**Original:** https://github.com/skarnov/mlm-software (CodeIgniter 2.x)
**New:** Laravel 13 + Blade + Alpine.js + Tailwind CSS 4

---

## Why This Project Matters

| Aspect | Impact |
|--------|--------|
| Industry | FinTech / Network Marketing |
| Complexity | Very High (MLM calculations, multi-level commissions) |
| Real-world | Used by actual agents for business |
| Skills | Complex business logic, financial systems, tree structures |

---

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

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 13, PHP 8.3 |
| Frontend | Blade + Alpine.js + Tailwind CSS 4 |
| Database | MySQL 8.0 |
| Auth | Laravel Sanctum + Spatie Permissions |
| Queue | Redis (commission calculations) |
| Exports | Laravel Excel / DomPDF |
| Docker | Docker + Docker Compose |
| Testing | PHPUnit + Pest |
| CI/CD | GitHub Actions |

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
| Type | Description | Calculation |
|------|-------------|-------------|
| Sponsor Bonus | Direct referral | 10% of CV amount |
| Matching Bonus | Binary tree match | 5% per matched pair |
| Level Commission | Multi-level | 2-5% per level (7 levels) |
| Royalty Bonus | Top performers | Monthly pool distribution |

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

## Development Timeline: 2.5 Weeks

### Week 1: Foundation
- Day 1-2: Laravel 13 setup, Docker, Auth, Roles
- Day 3: User management with sponsor tracking
- Day 4: CV system (tiers, purchases, activations)
- Day 5: PIN system (generate, assign, use)

### Week 2: Core Features
- Day 6-7: Binary tree structure + placement algorithm
- Day 8-9: Commission engine (sponsor, matching, levels)
- Day 10: Wallet system + withdrawal requests
- Day 11: Interest accrual system

### Week 3: Polish
- Day 12: Dashboard + reports
- Day 13: Support ticket system
- Day 14: Testing (Unit + Feature)
- Day 15: README with screenshots
- Day 16-17: Final testing & deployment

---

## README Highlights

### Badges
- Laravel 13
- PHP 8.3
- MySQL 8.0
- Docker
- PHPUnit

### Features
- Binary MLM tree structure
- Multi-level commission system
- CV & PIN management
- Wallet & withdrawal system
- Interest accrual
- Support tickets
- Dockerized setup

### Installation
```bash
git clone https://github.com/shaik-obydullah/mlm-software.git
cd mlm-software
composer install
cp .env.example .env
php artisan key:generate
docker-compose up -d
php artisan migrate --seed
php artisan serve
```

### Screenshots
- Dashboard with earnings
- Binary tree visualization
- Commission reports
- Wallet transactions

---

## Portfolio Value

### After This Addition (7 Laravel Projects)
```
Healthcare + Business + Data + Real Estate + Government + FinTech + NETWORK MARKETING
= "Senior Laravel developer with extensive industry experience"
```

### Interview Story
> "I rebuilt an MLM software from CodeIgniter to Laravel 13. The system handles binary tree placement, multi-level commissions, and real-time earnings calculations. It processes thousands of transactions daily for network marketing agents."

### Skills Demonstrated
| Skill | How It's Shown |
|-------|----------------|
| Laravel 13 | Modern framework features |
| Complex Business Logic | MLM calculations, commission engine |
| Tree Structures | Binary tree placement algorithm |
| Financial Systems | Wallet, transactions, interest |
| Queue Jobs | Background commission processing |
| Multi-level Relations | Upline/downline tracking |
| Testing | Unit + Feature tests |
| Docker | Production-ready setup |

---

## Comparison: Old vs New

| Aspect | Old (CodeIgniter) | New (Laravel 13) |
|--------|-------------------|------------------|
| Framework | CI 2.x (EOL) | Laravel 13 |
| Auth | MD5 | Bcrypt + Sanctum |
| Database | No relations | Migrations + FK |
| Frontend | Basic HTML | Blade + Alpine.js |
| Testing | None | PHPUnit + Pest |
| Security | Vulnerable | Production-ready |
| Architecture | Spaghetti | Service layer, SOLID |

---

## Ready to Build?

When ready, run:
```bash
# Create Laravel 13 project
composer create-project laravel/laravel mlm-software

# Install dependencies
composer require spatie/laravel-permission
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf

# Install Tailwind CSS + Alpine.js
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```
