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
        ],
        'procurement_officer' => [
            'label' => 'Procurement Officer',
            'subtitle' => 'Source medicines, ship to Lae AMS & manage orders',
            'inventory_level' => 'ndoh',
            'inventory_label' => 'NDoH Central Store',
            'dashboard_route' => 'dashboard.procurement_officer',
        ],
        'store_manager' => [
            'label' => 'Store Manager',
            'subtitle' => 'Lae AMS warehouse operations',
            'inventory_level' => 'lae_ams',
            'inventory_label' => 'Lae AMS Warehouse',
            'dashboard_route' => 'dashboard.store_manager',
        ],
        'pharmacy_manager' => [
            'label' => 'Pharmacy Manager',
            'subtitle' => 'Modilon Hospital pharmacy',
            'inventory_level' => 'modilon_hospital',
            'inventory_label' => 'Modilon Hospital',
            'dashboard_route' => 'dashboard.pharmacy_manager',
        ],
        'pharmacist' => [
            'label' => 'Pharmacist',
            'subtitle' => 'Dispensing & stock checks',
            'inventory_level' => 'modilon_hospital',
            'inventory_label' => 'Modilon Hospital',
            'dashboard_route' => 'dashboard.pharmacist',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation sections
    |--------------------------------------------------------------------------
    */

    'sections' => [
        'overview' => 'Menu',
        'inventory' => 'Menu',
        'procurement' => 'Menu',
        'logistics' => 'Menu',
        'hospital' => 'Menu',
        'reports' => 'Other Menu',
        'administration' => 'Other Menu',
    ],

    'nav_groups' => [
        'menu' => ['overview', 'inventory', 'procurement', 'logistics', 'hospital'],
        'other' => ['reports', 'administration'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Supply chain nodes (dashboard visual)
    |--------------------------------------------------------------------------
    */

    'supply_chain' => [
        ['key' => 'ndoh', 'label' => 'NDoH', 'description' => 'Central procurement & shipments to Lae AMS'],
        ['key' => 'lae_ams', 'label' => 'Lae AMS', 'description' => 'Regional warehouse'],
        ['key' => 'modilon_hospital', 'label' => 'Modilon Hospital', 'description' => 'Patient dispensing'],
    ],

];
