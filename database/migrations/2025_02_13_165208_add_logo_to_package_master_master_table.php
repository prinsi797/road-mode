<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLogoToPackageMasterMasterTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('package_master_master', function (Blueprint $table) {
            $table->string('package_logo')->nullable();
            $table->foreignId('service_id')->constrained('service_cat_master')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('package_master_master', function (Blueprint $table) {
            //
        });
    }
}
