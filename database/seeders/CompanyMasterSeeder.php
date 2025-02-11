<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class CompanyMasterSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::table('company_master')->insert([
            [
                'com_code' => 'CMP001',
                'com_name' => 'Honda',
                'com_logo' => 'images/company/01.jpg',
                'vehical_id' => 1, // Ensure this vehicle ID exists
                'is_status' => '1', // Active
                'created_by' => 'Admin',
                'modified_by' => 'Admin',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'com_code' => 'CMP002',
                'com_name' => 'Toyota',
                'com_logo' => 'images/company/02.jpg',
                'vehical_id' => 2, // Ensure this vehicle ID exists
                'is_status' => '0', // Inactive
                'created_by' => 'Admin',
                'modified_by' => 'Admin',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'com_code' => 'CMP003',
                'com_name' => 'BMW',
                'com_logo' => 'images/company/03.png',
                'vehical_id' => 3, // Ensure this vehicle ID exists
                'is_status' => '1', // Active
                'created_by' => 'Admin',
                'modified_by' => 'Admin',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}