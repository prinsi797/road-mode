<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageServiceMasterTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('package_service_master', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cust_id')->constrained('customer_master')->onDelete('cascade');
            $table->foreignId('pack_id')->constrained('package_master')->onDelete('cascade');
            $table->date('service_date')->nullable();
            $table->string('job_no_id')->nullable();
            $table->date('actual_date')->nullable();
            $table->string('is_service_done')->nullable();
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
        Schema::dropIfExists('package_service_master');
    }
}
