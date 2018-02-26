<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Library extends Model
{
    public function owner()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function libraries()
    {
        return $this->morphMany('App\Library', 'item');
    }

    public function event()
    {
        return $this->belongsTo('App\Event', 'item_id', 'id', 'item_type');
    }

    public function venue()
    {
        return $this->belongsTo('App\Venue', 'item_id', 'id', 'item_type');
    }

    public function schedule()
    {
        return $this->belongsTo('App\Schedule', 'item_id', 'id', 'item_type');
    }
}
