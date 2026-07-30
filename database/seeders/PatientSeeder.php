<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::where('email', 'pharmacist@modilon.gov.pg')->first()
            ?? User::where('email', 'pharmacy.manager@modilon.gov.pg')->first()
            ?? User::first();

        $patients = [
            [
                'first_name' => 'Mary',
                'last_name' => 'Kila',
                'date_of_birth' => '1988-04-12',
                'gender' => 'female',
                'phone' => '67571234501',
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Waiko',
                'date_of_birth' => '1975-09-03',
                'gender' => 'male',
                'phone' => '67571234502',
            ],
            [
                'first_name' => 'Grace',
                'last_name' => 'Tamarua',
                'date_of_birth' => '2001-01-22',
                'gender' => 'female',
                'phone' => '67571234503',
            ],
            [
                'first_name' => 'Peter',
                'last_name' => 'Nali',
                'date_of_birth' => '1962-11-18',
                'gender' => 'male',
                'phone' => null,
            ],
            [
                'first_name' => 'Helen',
                'last_name' => 'Sipau',
                'date_of_birth' => '1995-06-30',
                'gender' => 'female',
                'phone' => '67571234505',
            ],
        ];

        foreach ($patients as $data) {
            $exists = Patient::where('first_name', $data['first_name'])
                ->where('last_name', $data['last_name'])
                ->whereDate('date_of_birth', $data['date_of_birth'])
                ->exists();

            if ($exists) {
                continue;
            }

            Patient::create([
                'patient_number' => Patient::generatePatientNumber(),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'phone' => $data['phone'],
                'facility' => 'Modilon General Hospital, Madang',
                'is_active' => true,
                'created_by' => $creator?->id,
            ]);
        }
    }
}
