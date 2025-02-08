<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchMaster extends Model {
    use HasFactory, SoftDeletes;
    protected $table = "branch_master";
    protected $fillable = ['br_code', 'br_address', 'br_owner_name', 'br_owner_email', 'br_mobile', 'br_city', 'br_photo', 'br_sign', 'br_state', 'br_start_Date', 'br_end_date', 'br_renew_year', 'br_connection_link', 'br_db_name', 'br_user_name', 'br_password', 'br_city_id', 'br_area_id', 'br_pin_code', 'is_status', 'created_by', 'modified_by'];

    public function citymaster() {
        return $this->belongsTo(CityMaster::class, 'br_city_id');
    }

    public function areamaster() {
        return $this->belongsTo(AreaMaster::class, 'br_area_id');
    }
}
