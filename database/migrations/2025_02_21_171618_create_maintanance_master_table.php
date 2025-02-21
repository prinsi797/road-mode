<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintananceMasterTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('maintanance_master', function (Blueprint $table) {
            $table->id();
            $table->foreignId('main_cust_id')->constrained('customer_master')->onDelete('cascade');
            $table->string('main_is_oil_change')->nullable();
            $table->string('main_is_filter_change')->nullable();
            $table->string('main_is_washing')->nullable();
            $table->date('main_date')->nullable();
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
        Schema::dropIfExists('maintanance_master');
    }
}
