<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogsModel extends Model
{
    protected $table = 'tbl_logs';

    protected $fillable = [
        'id',
        'log_date',
        'device_id',
        'category',
        'message',
        'action',
        'is_read_user',
        'is_read_admin',
    ];

    public function devices()
    {
        return $this->belongsTo(DeviceModel::class, 'device_id', 'device_id');
    }



}