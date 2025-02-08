<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageMasterMasterTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('package_master_master', function (Blueprint $table) {
            $table->id();
            $table->string('pack_code')->nullable();
            $table->string('pack_name')->nullable();
            $table->string('pack_service_select')->nullable();
            $table->string('pack_other_faci')->nullable();
            $table->string('pack_description')->nullable();
            $table->string('pack_net_amt')->nullable();
            $table->string('pack_duration')->nullable();
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
        Schema::dropIfExists('package_master_master');
    }
}
