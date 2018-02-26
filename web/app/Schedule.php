<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'default' => 0,
    ];

    public function user()
    {
        // Many Schedules belong to many Users
        // note, select is used later for queries.
        return $this->belongsTo('App\User')->select(['id','name']);
    }

    public function events()
    {
        // One Schedule has many Events
        return $this->belongsToMany('App\Event')->withPivot([
            'created_at',
            'updated_at',
        ]);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = str_slug($value);
    }
}
