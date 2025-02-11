<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class VehicalTableSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::table('vehicles')->insert([
            'name' => "Car",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now()
        ]);
        DB::table('vehicles')->insert([
            'name' => "Bike",
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now()
        ]);
    }
}