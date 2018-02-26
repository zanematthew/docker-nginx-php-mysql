<?php

namespace App\Http\Controllers;

use App\Event;
use App\AbstractSearch;
use App\SearchInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SearchEventController extends AbstractSearch
{
    /**
     * Search for an Event by text;
     *   - filtered by Events greater than today
     *   - sorted by geo location
     *
     * @todo   needs test
     * @param  String $text    Search text.
     * @param  String $latlon  Geo-point as a string.
     * @return Object[         HTTP Json response
     */
    public function phrase(Request $request): \Illuminate\Http\JsonResponse
    {
        list($lat, $lon) = explode(',' , $request->latlon);
        return response()->json($this->formatResults(\Elasticsearch::searchTemplate([
            'body' => [
                'id' => 'event-suggest',
                'params' => [
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
                'id' => 'event-suggest',
                'params' => [
                    'lat'      => $lat,
                    'lon'      => $lon,
                    'latlon'   => $request->latlon,
                    'distance' => 250,
                ]
            ]
        ])));
    }

    public function date(Request $request): \Illuminate\Http\JsonResponse
    {
        list($lat, $lon) = explode(',' , $request->latlon);
        return response()->json($this->formatResults(\Elasticsearch::searchTemplate([
            'body' => [
                'id' => 'event-date',
                'params' => [
                    'to'       => $request->to,
                    'from'     => $request->from,
                    'lat'      => $lat,
                    'lon'      => $lon,
                    'latlon'   => $request->latlon,
                    'distance' => 250,
                ]
            ]
        ])));
    }
}