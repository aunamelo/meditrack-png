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

MediTrack PNG is a final-year Information Systems project developed to address critical gaps in the procurement, distribution, and dispensing of medicinal drugs across Papua New Guinea's public hospitals. Phase 1 is piloted in Madang Province, tracking drug deliveries from the National Department of Health (NDoH), through the Lae Area Medical Store (Lae AMS), to Modilon General Hospital.

Papua New Guinea's drug supply chain is currently managed through largely manual, fragmented processes with no integrated digital system providing real-time visibility — resulting in stock shortages, expired medication, theft, and inefficiencies that directly affect patient care. MediTrack PNG aims to digitize and integrate this chain, giving every stakeholder — from national administrators down to hospital pharmacists — real-time visibility into the movement of medicines relevant to their role:

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

MediTrack PNG is built on [Laravel 11](https://laravel.com/docs), using [Laravel Breeze](https://laravel.com/docs/starter-kits#laravel-breeze) for authentication and [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) for role-based access control. The frontend is styled with [Tailwind CSS](https://tailwindcss.com) and [Alpine.js](https://alpinejs.dev), built with [Vite](https://vitejs.dev), and backed by a [MySQL](https://www.mysql.com) database normalized to Third Normal Form (3NF).

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
npm run build
php artisan serve
```

### Assigning Roles

```bash
php artisan tinker
```
```php
$user = \App\Models\User::find(1);
$user->assignRole('admin'); // or: pharmacist, pharmacy_manager, procurement_officer, store_manager
```

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
- NBC News PNG. (2023, November 22). *Medicine shortage affects health services in Madang.* NBC PNG.
- RNZ Pacific. (2025, December 11). *Illegal sales undermine essential medicine supply in PNG.* RNZ.
- Papua New Guinea Today. (2026). *Parliament report blames system failure for PNG drug shortages.* PNG Facts.

## License

Academic project — Divine Word University, Department of Information Systems. All rights reserved.