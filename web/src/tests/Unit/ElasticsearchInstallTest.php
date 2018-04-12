<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ElasticsearchInstallTest extends TestCase
{

    use RefreshDatabase;

    /**
     * Both the Scout searchable array and Elasticsearch index mapping must contain the same keys.
     *
     * @group elasticsearch-install
     * @test
     */
    public function event_searchable_array_mapping()
    {
        $event = factory(\App\Event::class)->create();
        $this->assertEmpty(array_diff(
            array_keys($event->toSearchableArray()),
            array_keys(\App\Event::elasticsearchMapping())
        ));
    }

    /**
     * Both the Scout searchable array and Elasticsearch index mapping must contain the same keys.
     *
     * @group elasticsearch-install
     * @test
     */
    public function venue_searchable_array_mapping()
    {
        $venue = factory(\App\Venue::class)->create();
        $this->assertEmpty(array_diff(
            array_keys($venue->toSearchableArray()),
            array_keys(\App\Venue::elasticsearchMapping())
        ));
    }

    /**
     * Verify the Elasticsearch index mapping contains the keys, values, and correct number of
     * items we are indexing.
     *
     * @group elasticsearch-install
     * @test
     */
    public function verify_elasticsearch_mappings()
    {
        $map = [
            'title'        => ['type' => 'text'],
            'type'         => ['type' => 'text'],
            'registration' => ['type' => 'date'],
            'name'         => ['type' => 'text'],
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
            'slug'         => ['type' => 'text'],
            'image_uri'    => ['type' => 'text'],
            'venue_id'     => ['type' => 'integer'],
            'venue_name'   => ['type' => 'text'],
        ];

        $config = config('elasticsearch.indexParams.body.mappings.doc.properties');

        $this->assertEquals(count($map), count($config));
        $this->assertArraySubset($map, $config);
    }
}
