<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhotoGallary extends Model {
    use HasFactory, SoftDeletes;
    protected $table = "photo_gallary_master";
    protected $fillable = ['photo_for', 'photo_name', 'photo_description', 'photo', 'is_status', 'created_by', 'modified_by'];
}