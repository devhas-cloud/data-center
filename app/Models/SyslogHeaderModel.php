<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyslogHeaderModel extends Model
{
    protected $table = 'tbl_syslog_header';
    protected $fillable = ['device_id', 'user_assigned', 'created_date', 'category', 'note', 'linked_file'];

    function details()
    {
        return $this->hasMany(SyslogDetailModel::class, 'header_id', 'id');
    }
    
    function user()
    {
        return $this->belongsTo(User::class, 'user_assigned', 'id');
    }
    
    function device()
    {
        return $this->belongsTo(DeviceModel::class, 'device_id', 'device_id');
    }
}
