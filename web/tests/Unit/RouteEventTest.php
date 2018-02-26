<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;

class RouteEventTest extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        Passport::actingAs(factory(\App\User::class)->create());
    }

    // /{id}/{slug}
    public function testSingleEvent()
    {
        $event = factory(\App\Event::class)->create();

        $response = $this->get(route('event.single', [
            'id'   => $event->id,
            'slug' => str_slug($event->title),
        ]));
        $response->assertJson([
            'id'                      => $event->id,
            'created_at'              => $event->created_at,
            'updated_at'              => $event->updated_at,
            'title'                   => $event->title,
            'type'                    => $event->type,
            'url'                     => $event->url,
            'fee'                     => $event->fee,
            'registration_start_time' => $event->registration_start_time,
            'registration_end_time'   => $event->registration_end_time,
            'start_date'              => $event->start_date,
            'end_date'                => $event->end_date,
            'flyer_uri'               => $event->flyer_uri,
            'event_schedule_uri'      => $event->event_schedule_uri,
            'hotel_uri'               => $event->hotel_uri,
            'usabmx_track_id'         => $event->usabmx_track_id,
            'usabmx_id'               => $event->usabmx_id,
            'venue_id'                => $event->venue_id,
            'venue' => [
                'id'              => $event->venue->id,
                'created_at'      => $event->venue->created_at,
                'updated_at'      => $event->venue->updated_at,
                'name'            => $event->venue->name,
                'district'        => $event->venue->district,
                'usabmx_id'       => $event->venue->usabmx_id,
                'website'         => $event->venue->website,
                'image_uri'       => $event->venue->image_uri,
                'description'     => $event->venue->description,
                'street_address'  => $event->venue->street_address,
                'zip_code'        => $event->venue->zip_code,
                'lat'             => $event->venue->lat,
                'long'            => $event->venue->long,
                'email'           => $event->venue->email,
                'primary_contact' => $event->venue->primary_contact,
                'phone_number'    => $event->venue->phone_number,
                'city_id'         => $event->venue->city_id,
                'city'            => [
                    'id'         => $event->venue->city->id,
                    'created_at' => $event->venue->city->created_at,
                    'updated_at' => $event->venue->city->updated_at,
                    'name'       => $event->venue->city->name,
                ]
            ]
        ]);
    }

    /**
     * Verify the events index will return events.
     *
     * @return void
     * @group route-event
     */
    public function testEvents()
    {
        factory(\App\Event::class, 2)->create([
            'start_date' => date('Y-m-d', strtotime('last month')),
        ]);
        factory(\App\Event::class)->create([
            'start_date' => date('Y-m-d', strtotime('today')),
        ]);
        factory(\App\Event::class, 3)->create([
            'start_date' => date('Y-m-d', strtotime('next month')),
        ]);

        $response = $this->get(route('events.index'));
        $response->assertJson(['total' => 6]);

        $response = $this->get(route('events.index', ['this_month' => true]));
        $response->assertJson(['total' => 1]);

        $response = $this->get(route('events.index', ['next_month' => true]));
        $response->assertJson(['total' => 3]);

        $response = $this->get(route('events.index', ['upcoming' => true]));
        $response->assertJson(['total' => 4]);
    }

    /**
     * Create 3 events, create 5 events in a given state. Verify the route
     * retrieves 5 events based on a state.
     *
     * @return void.
     * @group route-event
     */
    public function testEventsState()
    {
        $cityState = factory(\App\CityState::class)->create();
        $venueId = factory(\App\Venue::class)->create(['city_id' => $cityState->city_id])->id;
        factory(\App\Event::class, 3)->create([
            'start_date' => date('Y-m-d', strtotime('last month')),
            'venue_id'   => $venueId,
        ]);
        factory(\App\Event::class, 2)->create([
            'start_date' => date('Y-m-d', strtotime('today')),
            'venue_id'   => $venueId,
        ]);
        factory(\App\Event::class)->create([
            'start_date' => date('Y-m-d', strtotime('next month')),
            'venue_id'   => $venueId,
        ]);

        $stateAbbr = \App\State::find($cityState->state_id)->abbr;

        $response = $this->get(route('events.state', [
            'state' => $stateAbbr,
        ]));
        $response->assertJson(['total' => 6]);

        $response = $this->get(route('events.state', [
            'this_month' => true,
            'state'      => $stateAbbr
        ]));
        $response->assertJson(['total' => 2]);

        $response = $this->get(route('events.state', [
            'next_month' => true,
            'state'      => $stateAbbr
        ]));
        $response->assertJson(['total' => 1]);

        $response = $this->get(route('events.state', [
            'upcoming' => true,
            'state'    => $stateAbbr
        ]));
        $response->assertJson(['total' => 3]);
    }

    /**
     * Create 2 events with type "coffee", create 3 events. Verify the route
     * will return 3 events based on type.
     *
     * @return void
     * @group route-event
     */
    public function testType()
    {
        factory(\App\Event::class)->create([
            'start_date' => date('Y-m-d', strtotime('last month')),
            'type' => 'foo',
        ]);
        factory(\App\Event::class, 2)->create([
            'start_date' => date('Y-m-d', strtotime('today')),
            'type'       => 'foo',
        ]);
        factory(\App\Event::class, 4)->create([
            'start_date' => date('Y-m-d', strtotime('next month')),
            'type'       => 'foo',
        ]);

        $response = $this->get(route('events.type', ['type' => 'foo']));
        $response->assertJson(['total' => 7]);

        $response = $this->get(route('events.type', ['this_month' => true, 'type' => 'foo']));
        $response->assertJson(['total' => 2]);

        $response = $this->get(route('events.type', ['next_month' => true, 'type' => 'foo']));
        $response->assertJson(['total' => 4]);

        $response = $this->get(route('events.type', ['upcoming' => true, 'type' => 'foo']));
        $response->assertJson(['total' => 6]);
    }

    /**
     * Create 3 events withs a start date of 2020, create 2 events with a
     * start date of 2010. Verify route will return 3 events for 2020.
     *
     * @return void
     * @group route-event
     */
    public function testYear()
    {
        factory(\App\Event::class, 3)->create([
            'start_date' => '2020-03-01'
        ]);
        factory(\App\Event::class, 2)->create([
            'start_date' => '2010-03-01'
        ]);
        $response = $this->get(route('events.year', [
            'year' => 2020,
        ]));
        $response->assertJson([
            'total' => 3
        ]);
    }

    /**
     * Create multiple events in a given year, but in different months.
     * Verify the route will return the correct events when based in
     * the year and month.
     *
     * @return void.
     * @group route-event
     */
    public function testYearMonth()
    {
        factory(\App\Event::class, 3)->create([
            'start_date' => '2010-12-01'
        ]);
        factory(\App\Event::class, 2)->create([
            'start_date' => '2010-01-01'
        ]);
        $response = $this->get(route('events.year.month', [
            'year'  => 2010,
            'month' => '01',
        ]));
        $response->assertJson([
            'total' => 2
        ]);
    }

    /**
     * Create multiple events with the same type, but in different years.
     * Verify the correct events are returned when queried by year and
     * type.
     *
     * @return void.
     * @group route-event
     */
    public function testYearType()
    {
        factory(\App\Event::class, 2)->create([
            'start_date' => '2010-01-01',
            'type'       => 'foo',
        ]);
        factory(\App\Event::class, 3)->create([
            'start_date' => '2011-01-01',
            'type'       => 'bar',
        ]);
        $response = $this->get(route('events.year.type', [
            'year'  => 2010,
            'type' => 'foo',
        ]));
        $response->assertJson([
            'total' => 2
        ]);
    }

    /**
     * Create multiple events in the same year, but different states.
     * Verify correct events are returned based on year and state.
     *
     * @return void.
     * @group route-event
     */
    public function testEventsYearState()
    {
        $cityStateA = factory(\App\CityState::class)->create();
        factory(\App\Event::class, 3)->create([
            'start_date' => '2016-01-01 01:01:01',
            'venue_id'   => factory(\App\Venue::class)->create(['city_id' => $cityStateA->city_id])->city_id,
        ]);

        $cityStateB = factory(\App\CityState::class)->create();
        factory(\App\Event::class, 2)->create([
            'start_date' => '2016-01-01 01:01:01',
            'venue_id'   => factory(\App\Venue::class)->create(['city_id' => $cityStateB->city_id])->city_id,
        ]);

        $response = $this->get(route('events.year.state', [
            'year'  => 2016,
            'state' => \App\State::find($cityStateA->state_id)->abbr,
        ]));

        $response->assertJson([
            'total' => 3
        ]);
    }

    /**
     * Create multiple events in the same year, and state, but different types. Verify
     * when queried on state/year/type the correct events are returned.
     *
     * @return void.
     * @group route-event
     */
    public function testEventsYearTypeState()
    {
        $cityStateA = factory(\App\CityState::class)->create();
        factory(\App\Event::class, 2)->create([
            'type'       => 'foo',
            'start_date' => '2017-01-01 01:01:01',
            'venue_id'   => factory(\App\Venue::class)->create(['city_id' => $cityStateA->city_id])->city_id,
        ]);
        $cityStateB = factory(\App\CityState::class)->create();
        factory(\App\Event::class, 3)->create([
            'type'       => 'bar',
            'start_date' => '2017-01-01 01:01:01',
            'venue_id'   => factory(\App\Venue::class)->create(['city_id' => $cityStateB->city_id])->city_id,
        ]);

        $response = $this->get(route('events.year.type.state', [
            'type' => 'foo',
            'year' => 2017,
            'state' => \App\State::find($cityStateA->state_id)->abbr,
        ]));

        $response->assertJson([
            'total' => 2,
        ]);
    }

    /**
     * Create events in the same year and month, but different states. Verify the correct
     * events are returned when queried by year, month, and state.
     *
     * @return void.
     * @group route-event
     */
    public function testEventsYearMonthState()
    {
        $cityStateA = factory(\App\CityState::class)->create();
        factory(\App\Event::class, 2)->create([
            'start_date' => '2016-06-01 01:01:01',
            'venue_id'   => factory(\App\Venue::class)->create(['city_id' => $cityStateA->city_id])->city_id,
        ]);
        $cityStateB = factory(\App\CityState::class)->create();
        factory(\App\Event::class, 3)->create([
            'start_date' => '2016-06-01 01:01:01',
            'venue_id'   => factory(\App\Venue::class)->create(['city_id' => $cityStateB->city_id])->city_id,
        ]);

        $response = $this->get(route('events.year.month.state', [
            'year'  => 2016,
            'month' => '06',
            'state' => \App\State::find($cityStateA->state_id)->abbr,
        ]));

        $response->assertJson([
            'total' => 2
        ]);
    }

    /**
     * Create events in the same year, month, state, but with different types.
     * Verify when queried by state the correct number is returned.
     *
     * @return void.
     * @group route-event
     */
    public function testEventsYearMonthTypeState()
    {
        $cityStateA = factory(\App\CityState::class)->create();
        factory(\App\Event::class, 2)->create([
            'start_date' => '2017-02-01 01:01:01',
            'type'       => 'foo',
            'venue_id'   => factory(\App\Venue::class)->create(['city_id' => $cityStateA->city_id])->city_id,
        ]);
        $cityStateB = factory(\App\CityState::class)->create();
        factory(\App\Event::class, 3)->create([
            'start_date' => '2017-02-01 01:01:01',
            'type'       => 'bar',
            'venue_id'   => factory(\App\Venue::class)->create(['city_id' => $cityStateB->city_id])->city_id,
        ]);

        $response = $this->get(route('events.year.month.type.state', [
            'year'  => 2017,
            'month' => '02',
            'type'  => 'foo',
            'state' => \App\State::find($cityStateA->state_id)->abbr,
        ]));
        $response->assertJson([
            'total' => 2,
        ]);
    }
}
