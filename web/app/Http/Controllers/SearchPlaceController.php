<?php

namespace App\Http\Controllers;

use App\Venue;
use App\Event;
use App\SearchInterface;
use App\AbstractSearch;

class SearchPlaceController extends AbstractSearch implements SearchInterface
{

    /**
     * A simple full text search.
     *
     * @todo   needs test
     * @param  String $text Search text
     * @return Object       HTTP Json response.
     */
    public function text($text): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            Venue::search($text)
            ->where('z_type', 'venue')
            ->take($this->take)
            ->get()
            ->load('city.states')
        );
    }

    /**
     * Sort search by proximity.
     *
     * @todo   needs test
     * @param  String $latlon A geo-point as a string.
     * @return Object         HTTP Json response.
     */
    public function proximity($latlon): \Illuminate\Http\JsonResponse
    {
        $results = Venue::search(
            '', function ($engine, $query, $options) use ($latlon) {
                $options['body']['sort']['_geo_distance'] = [
                'latlon'        => $latlon,
                'order'         => 'asc',
                'unit'          => 'mi',
                'mode'          => 'min',
                'distance_type' => 'arc',
                ];
                return $engine->search($options);
            }
        )->where('z_type', 'venue')->take($this->take)->get()->load('city.states');

        return response()->json($results);
    }

    public function textProximity($text, $latlon): \Illuminate\Http\JsonResponse
    {

    }
}
