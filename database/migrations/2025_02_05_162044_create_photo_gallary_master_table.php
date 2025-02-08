<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhotoGallaryMasterTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('photo_gallary_master', function (Blueprint $table) {
            $table->id();
            $table->string('photo_for')->nullable();
            $table->string('photo_name')->nullable();
            $table->string('photo_description')->nullable();
            $table->string('is_status')->nullable();
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
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
        Schema::dropIfExists('photo_gallary_master');
    }
}
