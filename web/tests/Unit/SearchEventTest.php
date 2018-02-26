<?php

namespace Tests\Unit;

use \App\Event;
use Tests\TestCase;
use Laravel\Passport\Passport;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchEventTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();
        Passport::actingAs(factory(\App\User::class)->create());
    }

    /**
     * @group search
     * @test
     */
    public function title_search() {

        // Build 5 events
        factory(Event::class)->create(['title' => 'Apple',      'start_date' => Carbon::today()->toDateTimeString()]);
        factory(Event::class)->create(['title' => 'Cherry',     'start_date' => Carbon::tomorrow()->toDateTimeString()]);
        factory(Event::class)->create(['title' => 'Strawberry', 'start_date' => Carbon::yesterday()->toDateTimeString()]);
        factory(Event::class)->create(['title' => 'Blueberry',  'start_date' => Carbon::tomorrow()->toDateTimeString()]);
        factory(Event::class)->create(['title' => 'Blackberry', 'start_date' => Carbon::today()->addWeek()->toDateTimeString()]);
        factory(Event::class)->create(['title' => 'Raspberry',  'start_date' => Carbon::today()->toDateTimeString()]);

        // keyword="berry"
        // Order should be chronological by start date
        // Only show upcoming events
        // Results:
        // Raspberry, today
        // Blueberry, tomorrow
        // Blackberry, next week
        //
        $response = $this->post(route('search.index', [
            'type'    => 'event',
            'keyword' => 'berry'
        ]), [
            'type'    => 'event',
            'keyword' => 'berry'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                ['title' => 'Raspberry'],
                ['title' => 'Blueberry'],
                ['title' => 'Blackberry'],
            ]);
    }

    // public function type_search() {}
    // public function fee_search() {}
    // public function start_date_search() {}
    // test for structure of API results
    // search:
    //   event:
    //     result: []
    //     keyword: ''
    //   venue:
    //     result: []
    //     keyword: ''
}
