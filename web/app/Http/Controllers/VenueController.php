<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VenueController extends Controller
{
    protected $paginate = 10;
    protected $params = [
        'page',
        'states',
    ];

    public function handleRequest($q = null)
    {
        if (request('states')) {
            // Validate
            $states = explode(',', request('states'));

            $q->whereHas('city.states', function ($query) use ($states) {
                $query->whereIn('abbr', $states);
            });
        }

        return $q;
    }


    public function index()
    {
        $q = \App\Venue::with('city.states', 'events');
        $q = $this->handleRequest($q);
        return $q->orderby('name', 'asc')
                 ->paginate($this->paginate)
                 ->appends(request($this->params));

        // return \App\Venue::with('city.states', 'events')->whereHas('events', function ($query) {
        //     $query->whereBetween('start_date', [
        //         date('Y-m-d', strtotime('today')),
        //         date('Y-m-d', strtotime('last day of this month')),
        //     ]);
        // })->orderby('name', 'asc')->paginate($this->paginate);
    }

    public function state($state = null)
    {
        return \App\Venue::with('city.states', 'events')->whereHas('city.states', function ($query) use ($state) {
            $query->where('abbr', strtoupper($state));
        })->paginate($this->paginate);
    }

    public function single($venueId = null)
    {
        return \App\Venue::with('events', 'city.states')->where('id', $venueId)->first();
    }

    public function events($venueId = null)
    {
        return \App\Event::where('venue_id', $venueId)->whereBetween('start_date', [
            date('Y-m-d', strtotime('today')),
            date('Y-m-d', strtotime('last day of this month')),
        ])->paginate($this->paginate);
    }
}
