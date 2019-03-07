<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class KerryPackageStatus extends Model
{
    protected $fillable = [
        'tracking_id',
        'receive_date',
        'status',
        'station',
        'message'
    ];
}
