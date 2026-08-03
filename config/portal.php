<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Portal roles
    |--------------------------------------------------------------------------
    |
    | Single source of truth for role labels, inventory scope, and dashboard copy.
    |
    */

    'roles' => [
        'admin' => [
            'label' => 'NDoH Admin',
            'subtitle' => 'National procurement, shipments & supply oversight',
            'inventory_level' => 'ndoh',
            'inventory_label' => 'NDoH Central Store',
            'dashboard_route' => 'dashboard.admin',
            'brand_icon' => 'images/ndoh-portal.png',
            'brand_alt' => 'National Department of Health — Papua New Guinea',
            'brand_tagline' => 'MediTrack PNG',
            'facility_group' => 'National Department of Health',
        ],
        'procurement_officer' => [
            'label' => 'Procurement Officer',
            'subtitle' => 'Source medicines, ship to Lae AMS & manage orders',
            'inventory_level' => 'ndoh',
            'inventory_label' => 'NDoH Central Store',
            'dashboard_route' => 'dashboard.procurement_officer',
            'brand_icon' => 'images/ndoh-portal.png',
            'brand_alt' => 'National Department of Health — Papua New Guinea',
            'brand_tagline' => 'MediTrack PNG',
            'facility_group' => 'National Department of Health',
        ],
        'store_manager' => [
            'label' => 'Store Manager',
            'subtitle' => 'Lae AMS warehouse operations',
            'inventory_level' => 'lae_ams',
            'inventory_label' => 'Lae AMS Warehouse',
            'dashboard_route' => 'dashboard.store_manager',
            'brand_icon' => 'images/ndoh-portal.png',
            'brand_alt' => 'Lae Area Medical Store — Papua New Guinea',
            'brand_tagline' => 'MediTrack PNG',
            'facility_group' => 'Lae Area Medical Store',
        ],
        'pharmacy_manager' => [
            'label' => 'Pharmacy Manager',
            'subtitle' => 'Modilon Hospital pharmacy',
            'inventory_level' => 'modilon_hospital',
            'inventory_label' => 'Pharmacy stock',
            'dashboard_route' => 'dashboard.pharmacy_manager',
            'brand_icon' => 'images/modilon-hospital.webp',
            'brand_alt' => 'Modilon General Hospital — Madang',
            'brand_tagline' => 'MediTrack PNG',
            'facility_group' => 'Modilon General Hospital',
        ],
        'pharmacist' => [
            'label' => 'Pharmacist',
            'subtitle' => 'Dispensing & stock checks',
            'inventory_level' => 'modilon_hospital',
            'inventory_label' => 'Pharmacy stock',
            'dashboard_route' => 'dashboard.pharmacist',
            'brand_icon' => 'images/modilon-hospital.webp',
            'brand_alt' => 'Modilon General Hospital — Madang',
            'brand_tagline' => 'MediTrack PNG',
            'facility_group' => 'Modilon General Hospital',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    |
    | Sidebar groups and link order are defined per role in
    | App\Services\PortalNavigationService (workflow order).
    |
    */

];
