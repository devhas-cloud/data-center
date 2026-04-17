<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyslogDetailModel extends Model
{
    protected $table = 'tbl_syslog_detail';
    protected $fillable = ['header_id', 'parameter_id', 'description'];

    function header()
    {
        return $this->belongsTo(SyslogHeaderModel::class, 'header_id', 'id');
    }

    function parameter()
    {
        return $this->belongsTo(ParameterModel::class, 'parameter_id', 'id');
    }
}
