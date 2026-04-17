<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoReportModel extends Model
{
    protected $table = 'tbl_auto_report';
    protected $fillable = [
        'device_id',
        'schedule_report',
        'email_report',
        'auto_report',
    ];

    public function device()
    {
        return $this->belongsTo(DeviceModel::class, 'device_id', 'device_id');
    }
}
