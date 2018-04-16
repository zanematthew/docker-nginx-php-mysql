<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CityState
 *
 * @mixin \Eloquent
 */
class CityState extends Model
{
    protected $fillable = [
        'city_id',
        'state_id',
    ];
}
