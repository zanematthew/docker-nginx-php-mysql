<?php

namespace Tests\Regression;

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

    /**
     * @return [type] [description]
     */
    public function testEventWithSameIdAsYear()
    {
        $event = factory(\App\Event::class)->create([
            'id'         => 2017,
            'start_date' => '2017-12-12',
            'title' => 'Event Title'
        ]);

        // These ID's are sequential
        factory(\App\Event::class, 5)->create();

        $response = $this->get(route('event.single', [
            'id'   => 2017,
            'slug' => str_slug('event-title')
        ]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
            ]);
    }
}
