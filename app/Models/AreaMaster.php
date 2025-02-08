<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AreaMaster extends Model {
    use HasFactory, SoftDeletes;
    protected $table = "area_master";
    protected $fillable = ['city_id', 'area_name', 'distance_from_branch'];

    public function city() {
        return $this->belongsTo(CityMaster::class, 'city_id');
    }
}
