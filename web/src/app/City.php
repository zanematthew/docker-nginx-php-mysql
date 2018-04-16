<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\City
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\State[] $states
 * @mixin \Eloquent
 */
class City extends Model
{
    protected $fillable = [
        'name',
    ];

    public function states()
    {
        return $this->belongsToMany('App\State', 'city_states');
    }
}
