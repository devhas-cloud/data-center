<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParameterModel extends Model
{
    protected $table = 'tbl_parameter';
    protected $fillable = ['parameter_label', 'parameter_name', 'parameter_unit', 'parameter_indicator_min', 'parameter_indicator_max'];
}
