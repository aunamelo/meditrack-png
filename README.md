MediTrack PNG

A web-based drug tracking system for Papua New Guinea's medicine supply chain

MediTrack PNG is a final-year Information Systems project developed to address critical gaps in the procurement, distribution, and dispensing of medicinal drugs across Papua New Guinea's public hospitals. Phase 1 is piloted in Madang Province.


Developed by Keith Banks Pala, Divine Word University — Department of Information Systems
Supervised by Mr. M. Cletus and Mr. J. Kupini



Background

Papua New Guinea's drug supply chain runs through multiple tiers: overseas procurement by the National Department of Health (NDoH), onward dispatch to one of five regional Area Medical Stores (AMS), and finally distribution to provincial hospitals and health facilities. For Madang Province, this chain passes through the Lae Area Medical Store (Lae AMS) in Morobe Province, which also supplies Eastern Highlands, Manus, and Northern Province.

This chain is currently managed through largely manual, fragmented processes with no integrated digital system providing real-time visibility. The result: stock shortages, expired medication, theft, and inefficiencies that directly affect patient care — problems documented in reporting from NBC PNG (2023), RNZ Pacific (2025), and a 2026 PNG Parliament report, and made starkly visible when a fire destroyed the Lae AMS, cutting off medicine supply to Madang and three other provinces.

Purpose

MediTrack PNG aims to digitize and integrate PNG's drug supply chain, giving every stakeholder — from national administrators down to hospital pharmacists — real-time visibility into the movement of medicines relevant to their role. The goal is to reduce stock shortages and waste, prevent theft and misuse, and support data-driven decisions by health authorities.

Project Scope

Phase 1 (current) is piloted in Madang Province, tracking drug deliveries along the chain:

NDoH (Port Moresby) → Lae Area Medical Store (Lae AMS) → Modilon General Hospital (Madang)

In scope for Phase 1:


Drug procurement recording
Inventory management with batch and expiry tracking
Distribution/transfer tracking between facilities
Dispensing records linked to prescriptions
Rule-based stock and expiry alerts
Reporting and analytics
Role-based user access control
System security and audit logging


Out of scope for Phase 1:


Financial systems integration
Mobile application
National, multi-province deployment (planned for future phases)


Roles & Organizational Levels

Access is fully role-based and enforced at the route level — each account is assigned exactly one role, and can only reach its own portal.

RoleLevelResponsibilitiesNDoH AdminNationalSystem administration, user and role managementProcurement OfficerNationalPurchase orders, supplier coordination, dispatch to AMSStore ManagerRegional (Lae AMS)Inventory control, stock receiving, warehouse/transfer managementPharmacy ManagerProvincial (Modilon General Hospital)Oversight of pharmacy-level operations and approvalsPharmacistProvincial (Modilon General Hospital)Drug dispensing linked to patient prescriptions

MVP Features


User authentication and role-based access control
Drug inventory management with batch tracking and expiry monitoring
Distribution tracking between the provincial store and hospital facilities
Dispensing records linked to patient prescriptions
Automated rule-based alerts for low stock and near-expiry drugs
Reporting dashboard for administrators


Predictive analytics, GPS-based delivery tracking, and financial system integration are planned for future phases beyond the current MVP.

System Architecture

MediTrack PNG is designed as a multi-tier web application using a hybrid cloud / on-premises architecture, chosen to balance centralized data access with the limited and unreliable internet connectivity at facilities like Modilon General Hospital.


Presentation tier — responsive Blade-templated frontend
Application tier — Laravel (PHP), handling business logic, authentication, and role-based access control
Data tier — MySQL, normalized to Third Normal Form (3NF)


The system's alerting component is implemented as a rule-based alert system: administrators configure thresholds (e.g. minimum stock levels, days-to-expiry) that automatically trigger notifications — a practical approach suited to a context where historical digital data for machine learning is not yet available.

Tech Stack


Framework: Laravel 11
Authentication: Laravel Breeze
Authorization: Spatie Laravel Permission
Frontend: Blade, Tailwind CSS, Alpine.js
Database: MySQL
Build tooling: Vite


Key Deliverables


Project Proposal Document
Requirements Specification
System Design Artifacts (use case, activity, and sequence diagrams; ERD; relational schemas; network diagrams)
Working Web Application (MVP)
Test Report (unit, integration, and user acceptance testing)
User Manual
Technical Documentation
Final Presentation


Getting Started

Prerequisites


PHP 8.2+
Composer
Node.js & npm
MySQL (or another supported RDBMS)


Installation

bashgit clone https://github.com/aunamelo/meditrack-png.git
cd meditrack-png

composer install
npm install

cp .env.example .env
php artisan key:generate

Configure your database credentials in .env, then run:

bashphp artisan migrate
npm run build
php artisan serve

Assigning Roles

Roles are managed via Spatie Permission:

bashphp artisan tinker

php$user = \App\Models\User::find(1);
$user->assignRole('admin'); // or: pharmacist, pharmacy_manager, procurement_officer, store_manager

Project Status

Under active development as a final-year project. Current focus is on core authentication and role-based access control; domain features (inventory, procurement, dispensing, alerts) are in progress per the project schedule.

References


NBC News PNG. (2023, November 22). Medicine shortage affects health services in Madang. NBC PNG.
RNZ Pacific. (2025, December 11). Illegal sales undermine essential medicine supply in PNG. RNZ.
Papua New Guinea Today. (2026). Parliament report blames system failure for PNG drug shortages. PNG Facts.


Academic Context

This project has been approved by the Department of Information Systems at Divine Word University and is developed as part of a final-year Information Systems project, supervised by Mr. M. Cletus and Mr. J. Kupini.

License

Academic project — Divine Word University, Department of Information Systems. All rights reserved.