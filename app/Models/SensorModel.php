<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorModel extends Model
{
    protected $table = 'tbl_sensor';
    protected $fillable = ['device_id', 'sensor_number', 'sensor_name',  'parameter_number', 'parameter_name','parameter_indicator_min','parameter_indicator_max','parameter_indicator_alert', 'sensor_unit', 'calibration_date', 'maintenance_date', 'status', 'notes'];

    function data()
    {
        return $this->hasMany(DataModel::class, 'device_id', 'device_id')
            ->where('parameter_name', $this->parameter_name);
    }

    function device()
    {
        return $this->belongsTo(DeviceModel::class, 'device_id', 'device_id');
    }

    function parameter()
    {
        return $this->belongsTo(ParameterModel::class, 'parameter_name', 'parameter_name'); 
    }

}
