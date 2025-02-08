<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchMasterTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('branch_master', function (Blueprint $table) {
            $table->id();
            $table->string('br_code')->nullable();
            $table->string('br_address')->nullable();
            $table->string('br_owner_name')->nullable();
            $table->string('br_owner_email')->nullable();
            $table->string('br_mobile')->nullable();
            $table->string('br_city')->nullable();
            $table->string('br_photo')->nullable();
            $table->string('br_sign')->nullable();
            $table->string('br_state')->nullable();
            $table->dateTime('br_start_Date')->nullable();
            $table->dateTime('br_end_date')->nullable();
            $table->date('br_renew_year')->nullable();
            $table->string('br_connection_link')->nullable();
            $table->string('br_db_name')->nullable();
            $table->string('br_user_name')->nullable();
            $table->string('br_password')->nullable();
            $table->foreignId('br_city_id')->constrained('city_master')->onDelete('cascade');
            $table->foreignId('br_area_id')->constrained('area_master')->onDelete('cascade');
            $table->string('br_pin_code')->nullable();
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
        Schema::dropIfExists('branch_master');
    }
}
