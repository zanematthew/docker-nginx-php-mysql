<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CityState extends Model
{
    protected $fillable = [
        'city_id',
        'state_id',
    ];
}
