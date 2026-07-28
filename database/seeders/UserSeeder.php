<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Portal demo users — matches local development accounts.
     * Default password for all: password
     */
    public function run(): void
    {
        $users = [
            [
                'role' => 'admin',
                'name' => 'Oathniel Konou',
                'email' => 'admin@health.gov.pg',
                'job_title' => 'NDoH Senior Administrator',
                'facility' => 'Department of Health Port Moresby',
                'phone' => '67571892308',
                'employee_id' => 'NDoH-2026-001',
            ],
            [
                'role' => 'procurement_officer',
                'name' => 'Bossie Taut',
                'email' => 'procurement@health.gov.pg',
            ],
            [
                'role' => 'store_manager',
                'name' => 'Bertha Kinavai',
                'email' => 'manager@lae-ams.health.gov.pg',
            ],
            [
                'role' => 'pharmacy_manager',
                'name' => 'Gabrielle Luckie',
                'email' => 'pharmacy.manager@modilon.gov.pg',
            ],
            [
                'role' => 'pharmacist',
                'name' => 'Pharmacist',
                'email' => 'pharmacist@modilon.gov.pg',
            ],
        ];

        foreach ($users as $data) {
            $role = Role::findOrCreate($data['role']);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'job_title' => $data['job_title'] ?? null,
                    'facility' => $data['facility'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'employee_id' => $data['employee_id'] ?? null,
                    'email_verified_at' => now(),
                    'password' => 'password',
                ]
            );

            $user->syncRoles([$role->name]);
        }
    }
}
