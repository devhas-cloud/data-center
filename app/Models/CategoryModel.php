<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{
    protected $table = 'tbl_category';
    protected $fillable = ['category_name', 'category_description', 'category_icon'];
    
    public function devices()
    {
        return $this->hasMany(DeviceModel::class, 'device_category', 'category_name');
    }

    public function access()
    {
        return $this->hasMany(AccessModel::class, 'category_id', 'category_id');
    }
}
