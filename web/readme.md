# Setup

1. Run migrations
2. Seed Database
3. Run Unit Test
4. Develop

# Database

## Migrations

## Seeding

# Models

All Models are defined using the [`php artian make:model <Model>`](https://laravel.com/docs/5.4/eloquent#defining-models) command.

This application has the following models:

    * City
        * One City belongs to many States
    * State
        * One State has many Cities
    * Event
        * Many Events belongs to many Schedules
        * One Event belongs to one Venue
    * Venue
        * One Venue has many Events
        * One Venue belongs to one City
    * Schedule
        * Many Schedules belongs to one user
    * User
        * One User has many Schedules

## Model Factories

Test data generated with [Faker](https://github.com/fzaninotto/Faker) will result in strange scenarios, i.e., Event start and end dates will date back into the 1900's.

**Fake Event**

An Event cannot exists without a Venue. When faking an Event a Venue, City, and State are created. Each item is related accordingly.

`factory(App\Event::class)->create(); // One Event`

`factory(App\Event::class, 5)->create(); // 5 Events, this will also create 5 Venues, Cities, and States`

**Fake Schedule, with Fake Events**

A fake Schedule will create a fake Event, along with needed relations.

`factory(App\EventSchedule::class)->create();`

# Routes

## API Routes

These are handled via Laravel. These are the routes for interacting with the database.

http://mybmx.events/api/event/{id}/{slug?}
http://mybmx.events/api/events/{state?}
http://mybmx.events/api/events/{year}/{state?}
http://mybmx.events/api/events/{year}/{month}/{state?}
http://mybmx.events/api/events/{year}/{type}/{state?}
http://mybmx.events/api/events/{year}/{month}/{type}/{state?}

http://mybmx.events/api/venue/{id}/{slug?}
http://mybmx.events/api/venues/{state?}

http://mybmx.events/{vue?}
Catch all for VueRouter.

## Front-end Routes

These are handled by VueJS.

http://mybmx.events/event/{id}/{slug?}
http://mybmx.events/events/{state?}
http://mybmx.events/events/{year}/{state?}
http://mybmx.events/events/{year}/{month}/{state?}
http://mybmx.events/events/{year}/{type}/{state?}
http://mybmx.events/events/{year}/{month}/{type}/{state?}

**Links**

* [Laravel Routes Documentation](https://laravel.com/docs/5.4/routing).
* List routes `php artisan route:list`.

## Events

**Plural**
`events/`, Return _all_ Events for the _current_ year (Y).
`events/<year>/`, Return _all_ Events for a given year (Y).
`events/<year>/<month>`, Return _all_ Events for a given year (Y), and month (n).
`events/<year>/<month>/<type>`

Plus state abbr

**Singular**
`event/<name>/<id>`, Return a single Event.

## Venues

`venues/`, Return _all_ Venues for _current_ state.
`venues/<state abbr>`, Return _all_ Venues by state abbreviation.
`venue/<name>/<id>`, Return a _single_ Venue prefixed with the name and Venue id.
`venue/<name>/events/<id>`, Return a _single_ Venue with _all_ Events for the _current_ Venue, and _current_ year (Y), prefixed with the Venue id.
`venue/<name>/events/<year>/<id>`, Return a _single_ Venue with _all_ Events for the _current_ Venue, year (Y) provided, prefixed with the Venue id.
`venue/<name>/events/<year>/<month>/<id>`, Return a _single_ Venue with _all_ Events for the _current_ venue, given a year (Y), and month (n) prefixed with the Venue id.

## Resource Routes

### Schedule

`schedule/`
`schedule/<id>/edit`
`schedule/<id>/add/<event id>/`

### Login, Register, Socialite

# Testing

`.env`, DB_DATABASE_TEST mysql_testing
`config/database.php`

# Notes

php artisan make:index-configurator MyIndexConfigurator
php artisan elastic:create-index "App\EventIndexConfigurator"
php artisan elastic:update-mapping App\\Event
php artisan scout:import "App\Event"

# Location Based search

```
App\Venue::search('', function($engine, $query, $options) {
    $options['body']['query']['bool']['filter']['geo_distance'] = [
        'distance' => '20km',
        'latlon'   => ['lat' => 39.290385, 'lon' => -76.612189],
    return $engine->search($options);
})-get()->load('city.states')->toArray();
```

# Elsaticsearch Notes

Just distance, sorted by closets
https://www.elastic.co/guide/en/elasticsearch/reference/5.4/search-request-sort.html
Search by location, then sort by distance.
Default is to show all venues that are closest to current location.
https://www.elastic.co/guide/en/elasticsearch/reference/5.4/search-request-sort.html

# Geolocation

Always uses geo-point as a string i.e., '123.45,-123.45'

# SSL

https://stackoverflow.com/a/44060726/714202
https://github.com/laravel/homestead/pull/527
Open the URL in question in Safari, allow the cert, restart Chrome, works.
https://localhost:8080/css/app.css

# ES Queries
#
# Event Text & Proximity Search
#
# pharse match prefix
# Sorted by closets
# Must be term "event"
#
GET /test_index/_search
{
  "query": {
    "bool": {
      "must": [
        {
          "multi_match": {
            "query": "Qual",
            "type": "phrase_prefix",
            "fields": ["title", "type", "city", "state"]
          }
        },
        {
          "range": {
            "registration": {
              "gte": "now"
            }
          }
        }
      ],
      "should": [
        {"term": { "z_type": { "value": "event" } } }
      ],
      "minimum_should_match": 1,
      "filter": {
        "geo_distance": {
          "distance": "100mi",
          "latlon": "39.2846225,-76.7605701"
        }
      }
    }
  },
  "size": 200
}

#
# Venue location search
#
# Show venus within a 100 mile radius, sorted by closest
#
GET /test_index/_search
{
  "query": {
    "bool": {
      "must": [
        { "match_all": {} }
      ],
      "filter": {
        "geo_distance": {
          "distance": "100mi",
          "latlon": "39.2846225,-76.7605701"
        }
      },
      "should": [
        { "term": {
          "z_type": {
            "value": "venue"
          }
        }}
      ],
      "minimum_should_match": 1
    }
  },
  "sort": [
    {
      "_geo_distance": {
        "latlon": "39.2846225,-76.7605701",
        "order": "asc"
      }
    }
  ]
}

#
# Venue Text search
#
# Search in; name
#
GET /test_index/_search
{
  "query": {
    "bool": {
      "must": [
        {
          "term": {
            "z_type": { "value": "venue" }
          }
        },
        {
          "match_phrase_prefix": {"name": "mar"}
        }
      ]
    }
  }
}

GET /test_index/_search
{
  "query": {
    "bool" : {
      "must" : {
        "range" : {
          "registration" : { "gte" : "now" }
        }
      },
      "should" : [
        { "term" : { "state": "Maryland" } },
        { "term" : { "name" : "Maryland" } },
        { "bool": {
          "must_not": [
            { "exists": {
              "field": "registration"
            }}
          ]
        } }
      ],
      "minimum_should_match" : 1,
      "boost" : 1.0
    }
  }
}

GET /test_index/_search
{
  "query": {
    "multi_match" : {
      "query":    "Maryland",
      "fields": [ "state", "city" ]
    }
  },
  "sort" : [
        {
            "_geo_distance" : {
                "latlon" : "38.6364668,-77.2934339",
                "order" : "asc",
                "unit" : "km",
                "mode" : "min",
                "distance_type" : "arc"
            }
        }
    ]
}

GET /test_index/_search
{
  "query": {
    "multi_match" : {
      "query":    "Maryland",
      "fields": [ "state", "city" ]
    }
  }
}

GET /test_index/_search
{
  "query": {
    "bool" : {
      "should" : [
        { "term" : { "state" : "Florida" } },
        { "term" : { "city" : "St Augustine" } }
      ],
      "minimum_should_match" : 1,
      "boost" : 1.0
    }
  }
}

GET /test_index/_search
{
    "query": {
        "range" : {
            "registration" : {
                "gte" : "now-1d/d",
                "lte" : "now/d",
                "boost" : 2.0
            }
        }
    }
}

GET /test_index/_search
{
  "query": {
    "bool": {
      "must": [
        {
          "range" : {
            "registration" : {
                "gte" : "now-1d/d",
                "lte" : "now/d",
                "boost" : 2.0
            }
          }
        }
      ],
      "should": [
        {
          "term": { "state": "Maryland"}
        }
      ]
    }
  },
    "sort" : [
        {
            "_geo_distance" : {
                "latlon" : "38.6364668,-77.2934339",
                "order" : "asc",
                "unit" : "km",
                "mode" : "min",
                "distance_type" : "arc"
            }
        }
    ]
}


GET /test_index/_search
{
  "query" : {
    "bool": {
      "must": [
        {
          "multi_match" : {
            "query":    "BMX",
            "fields": [ "state", "city", "name" ]
          }
        }
      ]
    }
  },
  "sort" : [
        {
            "_geo_distance" : {
                "latlon" : "38.6364668,-77.2934339",
                "order" : "asc",
                "unit" : "km",
                "mode" : "min",
                "distance_type" : "arc"
            }
        }
    ],
    "size": 50
}

GET /test_index/_search
{
    "query": {
        "bool" : {
            "must" : {
                "match_all" : {}
            },
            "filter" : {
                "geo_distance" : {
                    "distance" : "200km",
                    "latlon" : {
                        "lat" : 38.6364668,
                        "lon" : -77.2934339
                    }
                }
            }
        }
    },

    "sort" : [
        {
            "_geo_distance" : {
                "latlon" : "38.6364668,-77.2934339",
                "order" : "asc",
                "unit" : "km",
                "mode" : "min",
                "distance_type" : "arc"
            }
        }
    ],
    "size": 20
}

GET /test_index/_search
{
    "query": {
        "bool" : {
            "must" : {
                "match_all" : {}
            },
            "filter" : {
                "geo_distance" : {
                    "distance" : "200km",
                    "latlon" : "39.2628271,-76.6350047"
                }
            }
        }
    },
    "sort" : [
        {
            "_geo_distance" : {
                "latlon" : "39.2628271,-76.6350047",
                "order" : "asc",
                "unit" : "km",
                "mode" : "min",
                "distance_type" : "arc"
            }
        }
    ],
    "size": 20
}


#
# Venue - Suggestion
#
https://www.freeformatter.com/json-escape.html#ad-output
http://jsonviewer.stack.hu/
https://github.com/elastic/ansible-elasticsearch
https://fostermade.co/blog/testing-elasticsearch-and-simplifying-query-building
POST _scripts/venue-suggestion
{
  "script": {
    "lang": "mustache",
    "source": "{\"query\":{\"bool\":{\"must\":[{\"match_all\":{}}],\"filter\":{\"geo_distance\":{\"distance\":\"{{distance}}mi\",\"latlon\":\"{{#join}}latlon{{\/join}}\"}},\"should\":[{\"term\":{\"z_type\":{\"value\":\"venue\"}}}],\"minimum_should_match\":1}},\"sort\":[{\"_geo_distance\":{\"latlon\":\"{{#join}}latlon{{\/join}}\",\"order\":\"asc\"}}],\"_source\":true,\"script_fields\":{\"distance_from\":{\"script\":{\"source\":\"doc['latlon'].arcDistance(params.lat,params.lon) * 0.001\",\"lang\":\"painless\",\"params\":{\"lat\":{{lat}},\"lon\":{{lon}}}}}}}"
  }
}

GET test_index/_search/template
{
  "id": "venue-suggestion",
  "params": {
    "latlon": [39.2846225,-76.7605701],
    "lat": 39.2846225,
    "lon": -76.7605701,
    "distance": 20
  }
}

#
# Event -- Suggestion
#
# Greater than now
# Geo distance filter: 500mi
#
POST _scripts/event-suggestion
{
  "script": {
    "lang": "mustache",
    "source": "{\"query\":{\"bool\":{\"must\":[{\"range\":{\"registration\":{\"gte\":\"now\"}}}],\"should\":[{\"term\":{\"z_type\":{\"value\":\"event\"}}}],\"filter\":{\"geo_distance\":{\"distance\":\"{{distance}}mi\",\"latlon\":\"{{latlong}}\"}}}},\"sort\":[{\"_geo_distance\":{\"latlon\":\"{{latlong}}\",\"order\":\"asc\"}}],\"_source\":true,\"script_fields\":{\"distance_from\":{\"script\":{\"source\":\"doc['latlon'].arcDistance(params.lat,params.lon) * 0.001\",\"lang\":\"painless\",\"params\":{\"lat\":{{lat}},\"lon\":{{lon}}}}}}}"
  }
}

GET test_index/_search/template
{
  "id": "event-suggestion",
  "params": {
    "latlong": "39.2846225,-76.7605701",
    "lat": 39.2846225,
    "lon": -76.7605701,
    "distance": 100
  }
}

#
# Venue -- Basic Search Install @todo latlong
#
POST _scripts/venue-basic-search
{
  "script":{
    "lang": "mustache",
    "source": "{\"query\":{\"bool\":{\"must\":[{\"multi_match\":{\"type\":\"phrase_prefix\",\"query\":\"{{phrase}}\",\"fields\":[\"name\",\"state\"]}}],\"should\":[{\"term\":{\"z_type\":{\"value\":\"venue\"}}}],\"minimum_should_match\":1}},\"sort\":[{\"_geo_distance\":{\"latlon\":\"{{latlong}}\"}}],\"_source\":true,\"script_fields\":{\"distance_from\":{\"script\":{\"source\":\"doc['latlon'].arcDistance(params.lat,params.lon) * 0.001\",\"lang\":\"painless\",\"params\":{\"lat\":{{lat}},\"lon\":{{lon}}}}}}}"
  }
}

#
# Venue -- Basic Search Usage
#
GET test_index/_search/template
{
  "id": "venue-basic-search",
  "params": {
    "phrase": "new",
    "latlong": "39.2846225,-76.7605701",
    "lat": 39.2846225,
    "lon": -76.7605701
  }
}
```

# What problem are we solving?
# What is the technical philosophy?

Go back to feature branches
/Bring back the Venue icon
