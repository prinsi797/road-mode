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
            $table->unsignedBigInteger('inq_cust_id')->nullable();
            $table->date('inq_date')->nullable();
            $table->date('inq_pick_req_date')->nullable();
            $table->boolean('inq_slot_booking')->default(false);
            $table->text('inq_pick_address')->nullable();
            $table->text('inq_drop_address')->nullable();
            $table->string('inq_city')->nullable();
            $table->unsignedBigInteger('inq_branch_id')->nullable();
            $table->unsignedBigInteger('inq_package_id')->nullable();
            $table->unsignedBigInteger('inq_pks_s_id')->nullable();
            $table->unsignedBigInteger('inq_service_master_id')->nullable();
            $table->text('inq_des_from_customer')->nullable();
            $table->unsignedBigInteger('inq_pickup_man_id')->nullable();
            $table->string('inq_pick_tickit_code')->nullable();
            $table->string('inq_desk_audio_link')->nullable();
            $table->boolean('inq_is_confirm')->default(false);
            $table->timestamp('inq_is_confirm_timedate')->nullable();

            $table->string('is_status')->default(0);
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