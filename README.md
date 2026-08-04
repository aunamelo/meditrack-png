<p align="center">
<img src="https://raw.githubusercontent.com/aunamelo/meditrack-png/main/public/images/logo/meditrack-logo.png" width="320" alt="MediTrack PNG Logo" onerror="this.style.display='none'">
</p>

<h1 align="center">MediTrack PNG</h1>

<p align="center">
A web-based drug tracking system for Papua New Guinea's medicine supply chain
</p>

<p align="center">
<img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel&logoColor=white" alt="Laravel">
<img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat&logo=php&logoColor=white" alt="PHP">
<img src="https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?style=flat&logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
<img src="https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white" alt="MySQL">
<img src="https://img.shields.io/badge/Status-In%20Development-yellow?style=flat" alt="Status">
<img src="https://img.shields.io/badge/License-Academic-blue?style=flat" alt="License">
</p>

## About MediTrack PNG

MediTrack PNG is a final-year Information Systems project built to close critical gaps in how medicinal drugs are procured, distributed, and dispensed across Papua New Guinea's public hospitals. Phase 1 is piloted in Madang Province, following the movement of drug deliveries from the National Department of Health (NDoH), through the Lae Area Medical Store (Lae AMS), to Modilon General Hospital. Phase two of this project can be implemented to other 21 hospitals based on retrospectives from phase 1.

At present, PNG's drug supply chain relies on largely manual, disconnected processes, with no integrated digital system offering real-time oversight. This has contributed to stock shortages, expired medication, theft, and broader inefficiencies that ultimately affect patient care. MediTrack PNG sets out to digitize and unify this chain, giving every stakeholder — from national-level administrators to hospital pharmacists — real-time visibility into the medicines relevant to their role:

Role-based authentication and access control, maintaining strict separation across five distinct portals.
Drug inventory management, including batch tracking and expiry monitoring.
Distribution and transfer tracking between the regional store and hospital facilities.
Dispensing records tied directly to patient prescriptions.
Automated, rule-based alerts for low stock and drugs nearing expiry.
A reporting dashboard that gives administrators complete visibility across the supply chain.

The system is designed to be accessible, role-scoped, and reliable even in low-connectivity environments.

- [Role-based authentication and access control](#roles--organizational-levels), enforcing strict separation between five distinct portals.
- Drug inventory management with **batch tracking and expiry monitoring**.
- Distribution and transfer tracking between the regional store and hospital facilities.
- Dispensing records linked directly to patient prescriptions.
- Automated **rule-based alerts** for low stock and near-expiry drugs.
- A reporting dashboard giving administrators full visibility across the chain.

MediTrack PNG is accessible, role-scoped, and built to work reliably even where internet connectivity is limited.

## Project Scope

**Phase 1 (current)** is piloted in **Madang Province**, tracking drug deliveries along the chain:

```
NDoH (Port Moresby) → Lae Area Medical Store (Lae AMS) → Modilon General Hospital (Madang)
```

**In scope:** procurement recording, inventory management, distribution tracking, dispensing records, rule-based alerts, reporting, role-based access control, and audit logging.

**Out of scope (Phase 1):** financial systems integration, mobile application, national multi-province deployment — planned for future phases.

## Roles & Organizational Levels

| Role | Level | Responsibilities |
|---|---|---|
| **NDoH Admin** | National | System administration, user and role management |
| **Procurement Officer** | National | Purchase orders, supplier coordination, dispatch to AMS |
| **Store Manager** | Regional (Lae AMS) | Inventory control, stock receiving, warehouse/transfer management |
| **Pharmacy Manager** | Provincial (Modilon General Hospital) | Oversight of pharmacy-level operations and approvals |
| **Pharmacist** | Provincial (Modilon General Hospital) | Drug dispensing linked to patient prescriptions |

## Learning the Stack

MediTrack PNG is built with Laravel 11, using Laravel Breeze for authentication and Spatie Laravel Permission for role-based access control. The frontend uses Tailwind CSS and Alpine.js, built with Vite, and the database is MySQL, normalized to Third Normal Form (3NF). Alerts work on simple rules: administrators set thresholds — like minimum stock levels or days until expiry — and the system automatically sends a notification when those limits are reached. This rule-based approach was used instead of machine learning, since there isn't enough historical data yet to train a predictive model.

The system's alerting component is a **rule-based alert system** — administrators configure thresholds (minimum stock levels, days-to-expiry) that automatically trigger notifications, a practical approach suited to a context where historical digital data for machine learning is not yet available.

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL (or another supported RDBMS)

### Installation

```bash
git clone https://github.com/aunamelo/meditrack-png.git
cd meditrack-png

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Configure your database credentials in `.env`, then run:

```bash
php artisan migrate
php artisan db:seed
npm run build
php artisan serve
```

### Demo login accounts

After seeding, sign in with any account below. **Password for all:** `password`

| Role | Email |
|------|-------|
| NDoH Admin | `admin@health.gov.pg` |
| Procurement Officer | `procurement@health.gov.pg` |
| Store Manager (Lae AMS) | `manager@lae-ams.health.gov.pg` |
| Pharmacy Manager (Modilon) | `pharmacy.manager@modilon.gov.pg` |
| Pharmacist (Modilon) | `pharmacist@modilon.gov.pg` |

On a fresh server (Oracle, Replit, etc.), run `php artisan migrate --seed` after configuring `.env`.

### Deploy with Docker (Oracle VPS)

Full step-by-step reference (networking, `.env`, build, sync, migrations, troubleshooting):

**[docs/ORACLE_DOCKER_DEPLOYMENT.md](docs/ORACLE_DOCKER_DEPLOYMENT.md)**

Quick first deploy:

```bash
# On your Oracle VPS
sudo apt update && sudo apt install -y git docker.io docker-compose-v2
git clone https://github.com/aunamelo/meditrack-png.git
cd meditrack-png
cp .env.docker.example .env
nano .env   # set APP_URL, DB_PASSWORD, DB_ROOT_PASSWORD, RUN_SEED=true
docker compose up -d --build
```

After a successful first login, set `RUN_SEED=false` in `.env`.

Everyday update:

```text
PC:     git push
Oracle: git pull && docker compose up -d --build
```

### Assigning Roles

```bash
php artisan tinker
```
```php
$user = \App\Models\User::find(1);
$user->assignRole('admin'); // or: pharmacist, pharmacy_manager, procurement_officer, store_manager
```

## Entity Relationship Diagram

See **[docs/ERD.md](docs/ERD.md)** for the current database ERD (Mermaid), cardinality table, and design notes derived from migrations.

## Key Deliverables

- Project Proposal Document
- Requirements Specification
- System Design Artifacts (use case, activity, and sequence diagrams; ERD; relational schemas; network diagrams)
- Working Web Application (MVP)
- Test Report (unit, integration, and user acceptance testing)
- User Manual & Technical Documentation
- Final Presentation

## Academic Context

This project has been approved by the Department of Information Systems at Divine Word University and is developed as a final-year Information Systems project by **Keith Banks Pala**, supervised by **Mr. M. Cletus** and **Mr. J. Kupini**.

## References

- The National. (2026, March 26). *Medicine supply queried.* [The National](https://www.thenational.com.pg/medicine-supply-queried/)
- NBC News PNG. (2023, November 22). *Medicine shortage affects health services in Madang.* [NBC PNG](https://www.nbc.com.pg/post/10409/medicine-shortage-affects-health-services-in-madang)
- RNZ Pacific. (2025, December 11). *Illegal sales undermine essential medicine supply in PNG.* [RNZ](https://www.rnz.co.nz/news/pacific/581552/illegal-sales-undermine-essential-medicine-supply-in-png)
- Papua New Guinea Today. (2026, January 8). *Parliament report blames system failure for PNG drug shortages.* [Papua New Guinea Today](https://news.pngfacts.com/2026/01/parliament-report-blames-system-failure.html)

## License

Academic project — Divine Word University, Department of Information Systems. All rights reserved.
