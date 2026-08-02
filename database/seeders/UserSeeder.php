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
                'facility' => 'Department of Health, Port Moresby',
                'phone' => '67571892308',
                'employee_id' => 'NDoH-2026-001',
            ],
            [
                'role' => 'procurement_officer',
                'name' => 'Bossie Taut',
                'email' => 'procurement@health.gov.pg',
                'job_title' => 'NDoH Procurement Officer',
                'facility' => 'NDoH Central Procurement, Port Moresby',
                'phone' => '67572014562',
                'employee_id' => 'NDoH-2026-014',
            ],
            [
                'role' => 'store_manager',
                'name' => 'Bertha Kinavai',
                'email' => 'manager@lae-ams.health.gov.pg',
                'job_title' => 'Lae AMS Store Manager',
                'facility' => 'Lae Area Medical Store',
                'phone' => '67547288310',
                'employee_id' => 'AMS-LAE-2026-007',
            ],
            [
                'role' => 'pharmacy_manager',
                'name' => 'Gabrielle Luckie',
                'email' => 'pharmacy.manager@modilon.gov.pg',
                'job_title' => 'Pharmacy Manager',
                'facility' => 'Modilon General Hospital, Madang',
                'phone' => '67542215690',
                'employee_id' => 'MGH-PHARM-2026-003',
            ],
            [
                'role' => 'pharmacist',
                'name' => 'Jonah Mavu',
                'email' => 'pharmacist@modilon.gov.pg',
                'job_title' => 'Dispensing Pharmacist',
                'facility' => 'Modilon General Hospital, Madang',
                'phone' => '67542215714',
                'employee_id' => 'MGH-PHARM-2026-011',
            ],
        ];

        foreach ($users as $data) {
            $role = Role::findOrCreate($data['role']);

            $user = User::withTrashed()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'job_title' => $data['job_title'],
                    'facility' => $data['facility'],
                    'phone' => $data['phone'],
                    'employee_id' => $data['employee_id'],
                    'email_verified_at' => now(),
                    'password' => 'password',
                    'deleted_at' => null,
                ]
            );

            $user->syncRoles([$role->name]);
        }
    }
}
