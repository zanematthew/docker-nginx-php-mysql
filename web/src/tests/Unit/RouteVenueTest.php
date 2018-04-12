<?php

namespace Tests\Feature\tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;

class RouteVenueTest extends TestCase
{

    use DatabaseMigrations;
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        Passport::actingAs(factory(\App\User::class)->create());
    }

    public function testSingleVenue()
    {
        $venue = factory(\App\Venue::class)->create();
        $events = factory(\App\Event::class, 6)->create([
            'venue_id' => $venue->id,
        ]);

        $response = $this->get(route('venue.single', [
            'id' => $venue->id,
        ]));
        $response->assertStatus(200);
    }

    public function testStateVenues()
    {
        factory(\App\Venue::class, 4)->create();

        $cityState = factory(\App\CityState::class)->create();
        factory(\App\Venue::class, 2)->create([
            'city_id' => $cityState->city_id,
        ]);

        $state = \App\State::find($cityState->state_id);
        $response = $this->get(route('venues.state', [
            'state' => $state->abbr,
        ]));
        $this->assertCount(2, $response->decodeResponseJson()['data']);
    }
}
