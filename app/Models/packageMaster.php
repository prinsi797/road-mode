<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class packageMaster extends Model {
    use HasFactory, SoftDeletes;
    protected $table = "package_master_master";
    protected $fillable = ['pack_code', 'pack_name', 'pack_service_select', 'pack_other_faci', 'pack_description', 'pack_net_amt', 'pack_duration', 'is_status', 'package_logo', 'service_id', 'created_by', 'modified_by'];

    public function service() {
        return $this->belongsTo(ServiceCategory::class, 'service_id');
    }
}