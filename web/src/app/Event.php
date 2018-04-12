<?php

namespace App;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{

    use Searchable;

    protected $fillable = [
        'title',
        'type',
        'url',
        'fee',
        'registration_start_time',
        'registration_end_time',
        'start_date',
        'end_date',
        'flyer_uri',
        'event_schedule_uri',
        'hotel_uri',
        'usabmx_track_id',
        'usabmx_id',
    ];

    protected $appends = [
        'slug',
        'type_name',
    ];

    public function schedules()
    {
        return $this->belongsToMany('App\Schedule');
    }

    public function venue()
    {
        return $this->belongsTo('App\Venue', 'venue_id');
    }

    public function getSlugAttribute()
    {
        return str_slug($this->title);
    }

    public function getTypeNameAttribute()
    {
        return ucwords(str_replace('-', ' ', $this->type));
    }

    /**
     * The allowed keys that are being searched, and the
     * any formatting if needed.
     *
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/date.html
     */
    public function toSearchableArray(): array
    {
        return [
            'title'        => $this->title,
            'type'         => $this->type,
            'registration' => date('Y-m-d\TH:i:s\Z', strtotime($this->start_date .' '. $this->registration_start_time)),
            'latitude'     => $this->venue->lat,
            'longitude'    => $this->venue->long,
            'latlon'       => sprintf('%f,%f', $this->venue->lat, $this->venue->long),
            'city'         => $this->venue->city->name,
            'state'        => $this->venue->city->states()->first()->name ?? null,
            'state_abbr'   => $this->venue->city->states()->first()->abbr ?? null,
            'z_type'       => 'event',
            'created_at'   => date('Y-m-d\TH:i:s\Z', time()),
            'id'           => $this->id,
            'image_uri'    => $this->venue->image_uri,
            'venue_name'   => $this->venue->name,
            'slug'         => $this->slug,
            'venue_id'     => $this->venue->id,
        ];
    }

    public static function elasticsearchMapping(): array
    {
        return [
            'title'        => ['type' => 'text'],
            'type'         => ['type' => 'text'],
            'registration' => ['type' => 'date'],
            'zip_code'     => ['type' => 'integer'],
            'latitude'     => ['type' => 'float'],
            'longitude'    => ['type' => 'float'],
            'latlon'       => ['type' => 'geo_point'],
            'city'         => ['type' => 'text'],
            'state'        => ['type' => 'text'],
            'state_abbr'   => ['type' => 'text'],
            'z_type'       => ['type' => 'keyword'],
            'created_at'   => ['type' => 'date'],
            'id'           => ['type' => 'integer'],
            'image_uri'    => ['type' => 'text'],
            'venue_name'   => ['type' => 'text'],
            'slug'         => ['type' => 'text'],
            'venue_id'     => ['type' => 'integer'],
        ];
    }
}
