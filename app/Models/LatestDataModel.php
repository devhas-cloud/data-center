<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LatestDataModel extends Model
{
    protected $table = 'tbl_latest_data';
    protected $fillable = ['recorded_at', 'timestamp', 'device_id', 'parameter_name', 'value'];

    function sensor()
    {
        return $this->belongsTo(SensorModel::class, 'device_id', 'device_id')
            ->where('parameter_name', $this->parameter_name);
    }

    function device()
    {
        return $this->belongsTo(DeviceModel::class, 'device_id', 'device_id');
    }
}
