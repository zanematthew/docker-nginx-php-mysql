<?php

namespace App;

/**
 * This class will contain our Elasticsearch Templates.
 *
 * Its easiest/quickest to;
 * 1. Build and test the queries in Kibana
 * 2. Manually convert the queries to arrays
 * 3. Use an online tool to trim white space and escape quotes if need.
 * 4. Update this file.
 *
 * @note
 * Distance is calculated using arcDistance script, which needs lat/lon as a numbers, other sections
 * need lat/lon as string geo-point. Hence why lat/lon is passed in twice, and why the "source" is converted
 * to a string. See ES docs.
 *
 * @note
 * Template names are the kebab-case equivalent of the method name.
 *
 * @note
 * See method comments for params.
 *
 * @references
 * https://www.elastic.co/guide/en/elasticsearch/reference/current/search-template.html
 * https://github.com/elastic/elasticsearch-php/issues/686#issuecomment-348212032
 * https://www.freeformatter.com/json-escape.html
 * http://jsonviewer.stack.hu/
 *
 */
class ElasticsearchSearchTemplates
{
    /**
     * Search Events by date (to, from),
     * limited to a given mile radius,
     * sorted by date ASC.
     *
     * Params:
     *     {{from}}
     *     {{to}}
     *     {{lat}}
     *     {{lon}}
     *     {{latlon}}
     *     {{distance}}
     */
    public function eventDate()
    {
        return [
            'id'   => kebab_case(__FUNCTION__),
            'body' => [
                'script' => [
                    'lang'   => 'mustache',
                    'source' => "{\"sort\":[{\"registration\":{\"order\":\"asc\"}}],\"query\":{\"bool\":{\"must\":[{\"range\":{\"registration\":{\"gte\":\"{{from}}\",\"lte\":\"{{to}}\"}}}],\"should\":[{\"term\":{\"z_type\":{\"value\":\"event\"}}}],\"minimum_should_match\":1,\"filter\":{\"geo_distance\":{\"distance\":\"{{distance}}mi\",\"latlon\":\"39.2846225,-76.7605701\"}}}},\"_source\":true,\"script_fields\":{\"distance_from\":{\"script\":{\"source\":\"doc['latlon'].arcDistance(params.lat,params.lon) * 0.001\",\"lang\":\"painless\",\"params\":{\"lat\":{{lat}},\"lon\":{{lon}}}}}}}"
                ]
            ]
        ];
    }

    /**
     * Search for events by event title,
     * happening in the future,
     * within a given mile radius,
     * sorted by closets.
     *
     * Params
     *     {{phrase}}
     *     {{latlon}}
     *     {{lat}}
     *     {{lon}}
     *     {{distance}}
     */
    public function eventPhrase()
    {
        return [
            'id'   => kebab_case(__FUNCTION__),
            'body' => [
                'script' => [
                    'lang'   => 'mustache',
                    'source' => "{\"query\":{\"bool\":{\"must\":[{\"range\":{\"registration\":{\"gte\":\"now\"}}},{\"multi_match\":{\"type\":\"phrase_prefix\",\"query\":\"{{phrase}}\",\"fields\":[\"title\"]}}],\"should\":[{\"term\":{\"z_type\":{\"value\":\"event\"}}}],\"minimum_should_match\":1,\"filter\":{\"geo_distance\":{\"distance\":\"{{distance}}mi\",\"latlon\":\"{{latlon}}\"}}}},\"_source\":true,\"script_fields\":{\"distance_from\":{\"script\":{\"source\":\"doc['latlon'].arcDistance(params.lat,params.lon) * 0.001\",\"lang\":\"painless\",\"params\":{\"lat\":{{lat}},\"lon\":{{lon}}}}}}}"
                ]
            ]
        ];
    }

    /**
     * Events happening in the future,
     * within a 200 mile radius,
     * sorted by distance.
     *
     * Params
     *     {{params}}
     *     {{latlon}}
     *     {{lat}}
     *     {{lon}}
     *     {{distance}}
     */
    public function eventSuggest()
    {
        return  [
            'id'   => kebab_case(__FUNCTION__),
            'body' => [
                'script' => [
                    'lang' => 'mustache',
                    'source' => "{\"query\":{\"bool\":{\"must\":[{\"range\":{\"registration\":{\"gte\":\"now\"}}}],\"should\":[{\"term\":{\"z_type\":{\"value\":\"event\"}}}],\"filter\":{\"geo_distance\":{\"distance\":\"{{distance}}mi\",\"latlon\":\"{{latlon}}\"}}}},\"sort\":[{\"_geo_distance\":{\"latlon\":\"{{latlon}}\",\"order\":\"asc\"}}],\"_source\":true,\"script_fields\":{\"distance_from\":{\"script\":{\"source\":\"doc['latlon'].arcDistance(params.lat,params.lon) * 0.001\",\"lang\":\"painless\",\"params\":{\"lat\":{{lat}},\"lon\":{{lon}}}}}}}"
                ]
            ]
        ];
    }

    /**
     * Search Venue titles,
     * sorted by distance.
     *
     * Params
     *     {{phrase}}
     *     {{latlon}}
     *     {{lat}}
     *     {{lon}}
     */
    public function venuePhrase()
    {
        return [
            'id'   => kebab_case(__FUNCTION__),
            'body' => [
                'script' => [
                    'lang' => 'mustache',
                    'source' =>  "{\"query\":{\"bool\":{\"must\":[{\"multi_match\":{\"type\":\"phrase_prefix\",\"query\":\"{{phrase}}\",\"fields\":[\"name\",\"state\"]}}],\"should\":[{\"term\":{\"z_type\":{\"value\":\"venue\"}}}],\"minimum_should_match\":1}},\"sort\":[{\"_geo_distance\":{\"latlon\":\"{{latlon}}\"}}],\"_source\":true,\"script_fields\":{\"distance_from\":{\"script\":{\"source\":\"doc['latlon'].arcDistance(params.lat,params.lon) * 0.001\",\"lang\":\"painless\",\"params\":{\"lat\":{{lat}},\"lon\":{{lon}}}}}}}"
                ]
            ]
        ];
    }

    /**
     * Search for venues in 200 mile radius,
     * sorted by distance.
     *
     * Params
     *     {{latlon}}
     *     {{lat}}
     *     {{lon}}
     *     {{distance}}
     */
    public function venueSuggest()
    {
        return [
            'id'   => kebab_case(__FUNCTION__),
            'body' => [
                'script' => [
                    'lang' => 'mustache',
                    'source' => "{\"query\":{\"bool\":{\"must\":[{\"match_all\":{}}],\"filter\":{\"geo_distance\":{\"distance\":\"{{distance}}mi\",\"latlon\":\"{{latlon}}\"}},\"should\":[{\"term\":{\"z_type\":{\"value\":\"venue\"}}}],\"minimum_should_match\":1}},\"sort\":[{\"_geo_distance\":{\"latlon\":\"{{latlon}}\",\"order\":\"asc\"}}],\"_source\":true,\"script_fields\":{\"distance_from\":{\"script\":{\"source\":\"doc['latlon'].arcDistance(params.lat,params.lon) * 0.001\",\"lang\":\"painless\",\"params\":{\"lat\":{{lat}},\"lon\":{{lon}}}}}}}"
                ]
            ]
        ];
    }
}