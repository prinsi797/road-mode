<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyMasterTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('company_master', function (Blueprint $table) {
            $table->id();
            $table->string('com_code')->nullable();
            $table->string('bike_car')->nullable();
            $table->string('com_name')->nullable();
            $table->string('com_logo')->nullable();
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
        Schema::dropIfExists('company_master');
    }
}
