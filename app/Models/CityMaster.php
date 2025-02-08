<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CityMaster extends Model {
    use HasFactory, SoftDeletes;

    protected $table = "city_master";
    protected $fillable = ['city_name', 'created_by', 'modified_by', 'is_status'];

    public function area() {
        return $this->hasMany(AreaMaster::class, 'city_id');
    }
}
