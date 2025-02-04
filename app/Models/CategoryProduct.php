<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CategoryProduct extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = ['category_id', 'name', 'photo', 'price', 'sell_price', 'suggestion', 'description'];

    public function category() {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }
}