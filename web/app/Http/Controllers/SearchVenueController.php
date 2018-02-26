<?php

namespace App\Http\Controllers;

use App\Venue;
use App\AbstractSearch;
use App\SearchInterface;
use Illuminate\Http\Request;

class SearchVenueController extends AbstractSearch
{
    /**
     * A simple full text search.
     *
     * @todo   needs test
     * @param  String $text Search text
     * @return Object       HTTP Json response.
     */
    public function phrase(Request $request): \Illuminate\Http\JsonResponse
    {
        list($lat, $lon) = explode(',' , $request->latlon);
        return response()->json($this->formatResults(\Elasticsearch::searchTemplate([
            'body' => [
                'id' => 'venue-phrase',
                'params' => [
                    'phrase'   => $request->text,
                    'lat'      => $lat,
                    'lon'      => $lon,
                    'latlon'   => $request->latlon,
                    'distance' => 250,
                ]
            ]
        ])));
    }

    public function suggestion(Request $request): \Illuminate\Http\JsonResponse
    {
        list($lat, $lon) = explode(',' , $request->latlon);
        return response()->json($this->formatResults(\Elasticsearch::searchTemplate([
            'body' => [
                'id' => 'venue-suggest',
                'params' => [
                    'lat'      => $lat,
                    'lon'      => $lon,
                    'latlon'   => $request->latlon,
                    'distance' => 250,
                ]
            ]
        ])));
    }
}
