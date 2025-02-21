<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerMasterTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('customer_master', function (Blueprint $table) {
            $table->id();
            $table->string('cust_code')->nullable();
            $table->string('cust_name')->nullable();
            $table->string('cust_city')->nullable();
            $table->string('cust_res_address')->nullable();
            $table->string('cust_pick_default_addr')->nullable();
            $table->string('cust_email')->nullable();
            $table->foreignId('cust_for_branch_id')->constrained('branch_master')->onDelete('cascade');
            $table->string('cust_password')->nullable();
            $table->foreignId('cust_package_id')->constrained('package_master_master')->onDelete('cascade');
            $table->string('is_package_selected')->nullable();
            $table->date('cust_pack_start_date')->nullable();
            $table->date('cust_pack_end_date')->nullable();
            $table->string('cust_is_pack_renew')->nullable();
            $table->string('cust_is_noti_req')->nullable();
            $table->string('cust_mobile_no')->nullable();
            $table->string('cust_whtapp_no')->nullable();
            $table->foreignId('cust_com_id')->constrained('company_master')->onDelete('cascade');
            $table->foreignId('cust_model_id')->constrained('model_master')->onDelete('cascade');
            $table->string('cust_vehicle_no')->nullable();
            $table->string('is_pack_expire')->nullable();
            $table->string('is_renreable')->nullable();
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
        Schema::dropIfExists('customer_master');
    }
}
