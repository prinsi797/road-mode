<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InquiryMaster extends Model {
    use HasFactory, SoftDeletes;
    protected $table = 'inquiry_master';

    protected $fillable = [
        'inq_from_online_web',
        'inq_code',
        'inq_job_no_id',
        'inq_cust_id',
        'inq_date',
        'inq_pick_req_date',
        'inq_slot_booking',
        'inq_pick_address',
        'inq_drop_address',
        'inq_city',
        'inq_branch_id',
        'inq_package_id',
        'inq_pks_s_id',
        'inq_service_master_id',
        'inq_des_from_customer',
        'inq_pickup_man_id',
        'inq_pick_tickit_code',
        'inq_desk_audio_link',
        'inq_is_confirm',
        'inq_is_confirm_timedate',
        'is_status',
        'created_by',
        'modified_by',
        'modified_date',
        'is_deleted',
    ];
    public function branch() {
        return $this->belongsTo(BranchMaster::class, 'inq_branch_id');
    }

    public function package() {
        return $this->belongsTo(PackageMaster::class, 'inq_package_id');
    }

    public function customer() {
        return $this->belongsTo(CustomerMaster::class, 'inq_cust_id');
    }
}
