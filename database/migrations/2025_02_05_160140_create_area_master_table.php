<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreaMasterTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('area_master', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('city_master')->onDelete('cascade');
            $table->string('area_name')->nullable();
            $table->string('distance_from_branch')->nullable();
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
        Schema::dropIfExists('area_master');
    }
}
