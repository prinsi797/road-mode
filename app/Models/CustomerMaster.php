<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class CustomerMaster extends Authenticatable implements JWTSubject {
    use HasFactory;
    protected $table = "customer_master";
    protected $fillable = ['cust_code', 'cust_name', 'cust_city', 'cust_res_address', 'cust_pick_default_addr', 'cust_email', 'cust_password', 'cust_for_branch_id', 'cust_package_id', 'cust_mobile_no', 'cust_whtapp_no', 'cust_com_id', 'cust_model_id', 'cust_vehicle_no', 'is_status', 'created_by', 'modified_by'];

    public function getJWTIdentifier() {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }
}
