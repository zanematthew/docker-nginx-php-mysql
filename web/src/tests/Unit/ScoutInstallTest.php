<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScoutInstallTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify the class uses Laravel Scout
     *
     * @group scout-install
     * @test
     */
    public function event_uses_scout()
    {
        $this->assertTrue(in_array('Laravel\Scout\Searchable', class_uses('App\Event')));
    }

    /**
     * Verify that a searchable array does exists, and contains
     * the values we desire to search on.
     *
     * @group scout-install
     * @test
     */
    public function event_has_valid_searchable_array()
    {
        $event = factory(\App\Event::class)->create();

        $this->assertTrue([
            'title',
            'type',
            'registration',
            'latitude',
            'longitude',
            'latlon',
            'city',
            'state',
            'state_abbr',
            'z_type',
            'created_at',
            'id',
            'image_uri',
            'venue_name',
            'slug',
            'venue_id',
        ] === array_keys($event->toSearchableArray()));
    }

    /**
     * Verify the class uses Laravel Scout
     *
     * @group scout-install
     * @test
     */
    public function venue_uses_scout()
    {
        $this->assertTrue(in_array('Laravel\Scout\Searchable', class_uses('App\Venue')));
    }

    /**
     * Verify that a searchable array does exists, and contains
     * the values we desire to search on.
     *
     * @group scout-install
     * @test
     */
    public function venue_has_valid_searchable_array()
    {
        $venue = factory(\App\Venue::class)->create();

        $this->assertTrue([
            'title',
            'name',
            'zip_code',
            'latitude',
            'longitude',
            'latlon',
            'city',
            'state',
            'state_abbr',
            'z_type',
            'created_at',
            'id',
            'type',
            'registration',
            'slug',
            'image_uri',
        ] === array_keys($venue->toSearchableArray()));
    }
}
