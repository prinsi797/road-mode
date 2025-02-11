<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class ModelMasterSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::table('model_master')->insert([
            [
                'model_code' => 'MDL001',
                'model_name' => 'Civic',
                'model_photo' => 'models/civic.webp',
                'model_description' => 'Honda Civic 2024 Model',
                'is_status' => '1', // Active
                'created_by' => 'Admin',
                'modified_by' => 'Admin',
                'com_id' => 1, // Ensure this company ID exists
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'model_code' => 'MDL002',
                'model_name' => 'Corolla',
                'model_photo' => 'models/corolla.jpeg',
                'model_description' => 'Toyota Corolla 2024 Model',
                'is_status' => '0', // Inactive
                'created_by' => 'Admin',
                'modified_by' => 'Admin',
                'com_id' => 2, // Ensure this company ID exists
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'model_code' => 'MDL003',
                'model_name' => 'X5',
                'model_photo' => 'models/x5.jpeg',
                'model_description' => 'BMW X5 2024 Model',
                'is_status' => '1', // Active
                'created_by' => 'Admin',
                'modified_by' => 'Admin',
                'com_id' => 3, // Ensure this company ID exists
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}