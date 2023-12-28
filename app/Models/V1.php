<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class V1 extends Model
{
    use HasFactory, SoftDeletes;

    protected $table    = 'v1_s';
    protected $fillable = ['name', 'description', 'uuid'];
}
