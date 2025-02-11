<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyMaster extends Model {
    use HasFactory, SoftDeletes;
    protected $table = "company_master";
    protected $fillable = ['com_code', 'bike_car', 'com_name', 'com_logo', 'vehical_id', 'is_status', 'created_by', 'modified_by'];

    public function vehicle() {
        return $this->belongsTo(Vehicle::class, 'vehical_id');
    }
}