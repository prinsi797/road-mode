<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceCatMasterTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('service_cat_master', function (Blueprint $table) {
            $table->id();
            $table->string('sc_bike_car')->nullable();
            $table->string('sc_name')->nullable();
            $table->string('sc_photo')->nullable();
            $table->string('sc_description')->nullable();
            $table->string('is_status')->nullable();
            $table->string('created_by')->nullable();
            $table->String('modified_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('service_cat_master');
    }
}
