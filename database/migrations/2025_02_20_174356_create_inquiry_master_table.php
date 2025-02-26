<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInquiryMasterTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('inquiry_master', function (Blueprint $table) {
            $table->id();
            $table->string('inq_from_online_web')->nullable();
            $table->string('inq_code')->nullable();
            $table->unsignedBigInteger('inq_job_no_id')->nullable();
            $table->foreignId('inq_cust_id')->constrained('customer_master')->onDelete('cascade');
            $table->date('inq_date')->nullable();
            $table->date('inq_pick_req_date')->nullable();
            $table->boolean('inq_slot_booking')->default(false);
            $table->text('inq_pick_address')->nullable();
            $table->text('inq_drop_address')->nullable();
            $table->string('inq_city')->nullable();
            $table->foreignId('inq_branch_id')->constrained('branch_master')->onDelete('cascade')->nullable();
            $table->foreignId('inq_package_id')->constrained('package_master')->onDelete('cascade')->nullable();
            $table->foreignId('inq_pks_s_id')->constrained('package_service_master')->onDelete('cascade')->nullable();

            $table->unsignedBigInteger('inq_service_master_id')->nullable();
            $table->text('inq_des_from_customer')->nullable();
            $table->unsignedBigInteger('inq_pickup_man_id')->nullable();
            $table->string('inq_pick_tickit_code')->nullable();
            $table->string('inq_desk_audio_link')->nullable();
            $table->boolean('inq_is_confirm')->default(false);
            $table->timestamp('inq_is_confirm_timedate')->nullable();
            $table->integer('vehicle_id')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('model_id')->nullable();
            $table->string('is_status')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
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
        Schema::dropIfExists('inquiry_master');
    }
}