<?php

namespace App;

use App\Http\Controllers\Controller;

abstract class AbstractSearch extends Controller
{
    // Append extra fields
    // format distance from
    public function getExtraFields($item): array
    {
        if ($item['fields']['distance_from']) {
            $item['fields']['distance_from'] = round($item['fields']['distance_from'][0]);
        }

        return $item['fields'];
    }

    public function hasExtraFields($item): bool
    {
        return (isset($item['fields'])) ? true : false;
    }

    public function latlonAsArray($latlon): array
    {
        list($lat, $lon) = explode(',', $latlon);
        return [
            'lat' => floatval($lat),
            'lon' => floatval($lon),
        ];
    }

    public function formatResults($response): array
    {
        $hits = [];
        if ($response) {
            $hits = array_map(function ($item) {
                if ($this->hasExtraFields($item)) {
                    return array_merge($item['_source'], $this->getExtraFields($item));
                }
                return $item['_source'];
            }, $response['hits']['hits']);
        }
        return $hits;
    }
}