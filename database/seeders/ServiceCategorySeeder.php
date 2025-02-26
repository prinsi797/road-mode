<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::table('service_cat_master')->insert([
            [
                'sc_bike_car' => 'Bike',
                'sc_name' => 'Oil Change',
                'sc_photo' => 'images/services/oil_change.jpg',
                'sc_description' => 'Regular bike oil change service.',
                'vehical_id' => 1,
                'is_status' => '1',
                'created_by' => 'Administrator',
                'modified_by' => 'Administrator',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'sc_bike_car' => 'Car',
                'sc_name' => 'Car Wash',
                'sc_photo' => 'images/services/car_wash.jpg',
                'sc_description' => 'Premium car wash service.',
                'vehical_id' => 2,
                'is_status' => '1',
                'created_by' => 'Administrator',
                'modified_by' => 'Administrator',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'sc_bike_car' => 'Bike',
                'sc_name' => 'Brake Service',
                'sc_photo' => 'images/services/brake_service.jpg',
                'sc_description' => 'Brake pads replacement and tuning.',
                'vehical_id' => 1,
                'is_status' => '0',
                'created_by' => 'Administrator',
                'modified_by' => 'Administrator',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}