<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceModel extends Model
{
    protected $table = 'tbl_device';
    protected $fillable = ['device_id', 'device_category', 'user_assigned', 'device_name', 'location', 'district', 'latitude', 'longitude', 'device_ip', 'device_gap_timeout', 'date_installation', 'linked_img'];

    function sensors()
    {
        return $this->hasMany(SensorModel::class, 'device_id', 'device_id');
    }

    function data()
    {
        return $this->hasMany(DataModel::class, 'device_id', 'device_id');
    }

    function category(){
        return $this->belongsTo(CategoryModel::class, 'device_category', 'category_name');
    }

    function user(){
        return $this->belongsTo(User::class, 'user_assigned', 'id');
    }

    function access()
    {
        return $this->hasMany(AccessModel::class, 'device_id', 'device_id');
    }

    function logs()
    {
        return $this->hasMany(LogsModel::class, 'device_id', 'device_id');
    }

}
