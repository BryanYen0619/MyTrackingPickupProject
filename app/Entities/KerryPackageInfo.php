<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class KerryPackageInfo extends Model
{
    protected $fillable = [
        'tracking_id',
        'piece',
        'carton_id'
    ];
}
