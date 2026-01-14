<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessModel extends Model
{
    protected $table = 'tbl_access';

    protected $fillable = [
        'device_id',
        'category_id',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function device()
    {
        return $this->belongsTo(DeviceModel::class, 'device_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(CategoryModel::class, 'category_id', 'id');
    }

}
