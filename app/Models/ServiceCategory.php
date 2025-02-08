<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCategory extends Model {
    use HasFactory, SoftDeletes;

    protected $table = "service_cat_master";
    protected $fillable = ['sc_name', 'sc_bike_car', 'sc_photo', 'sc_description', 'is_status', 'created_by', 'modified_by'];

    public function products() {
        return $this->hasMany(CategoryProduct::class, 'category_id');
    }
}
