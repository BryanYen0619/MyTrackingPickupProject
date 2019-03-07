<?php
/**
 * Created by PhpStorm.
 * User: bryan.yen
 * Date: 2018/11/15
 * Time: 11:37 AM
 */

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class KerryPackageCartonInfo extends Model
{
    protected $fillable = [
        'carton_size',
        'price'
    ];
}
