<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelMaster extends Model {
    use HasFactory, SoftDeletes;
    protected $table = "model_master";
    protected $fillable = ['model_code', 'model_name', 'model_photo', '	model_description', 'is_status', 'com_id',  'created_by', 'modified_by'];

    public function companymaster() {
        return $this->belongsTo(CompanyMaster::class, 'com_id');
    }
}