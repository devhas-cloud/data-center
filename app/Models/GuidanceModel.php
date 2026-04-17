<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuidanceModel extends Model
{
    /** @use HasFactory<\Database\Factories\GuidanceModelFactory> */
    use HasFactory;

    protected $table = 'tbl_guidance';
    protected $fillable = ['title', 'description', 'image_path', 'content', 'link_path'];
    

}
