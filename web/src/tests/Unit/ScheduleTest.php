<?php

namespace Tests\Unit;

use Tests\TestCase;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    // Create 7 schedules total
    // Assign 3 to a random user from the factory
    // Assign 4 to our created user.
    // Verify the HTTP status code (200)
    // Verify the returned JSON structure
    // Verify the count
    public function testIndex()
    {
        $user = factory(\App\User::class)->create();
        Passport::actingAs($user);

        factory(\App\Schedule::class, 1)->create();
        factory(\App\Schedule::class, 2)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('user.schedule.index'));
        $response
            ->assertStatus(200)
            ->assertJsonStructure([[
                'id',
                'created_at',
                'updated_at',
                'name',
                'user_id',
                'default',
                'slug',
            ]]);
        $this->assertCount(2, $response->getData());
    }

    /**
     * @group schedule
     */
    public function testStore()
    {
        Passport::actingAs(factory(\App\User::class)->create());

        $response = $this->post(route('user.schedule.store'), [
            'name' => 'My Awesome Name'
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'created_at',
                'updated_at',
                'events',
        ]);
    }

    public function testUpdate()
    {
        // Create user
        // Sign into API.
        // Create and assign a generated schedule
        $user = factory(\App\User::class)->create();
        Passport::actingAs($user);
        $schedule = factory(\App\Schedule::class)->create([
            'user_id' => $user->id,
        ]);
        // Store the current name
        $oldName = $schedule->name;
        // Send the HTTP post request with the schedule ID as the route param, and the
        // name as the request
        $response = $this->post(route('user.schedule.update', [
            'id' => $schedule->id
        ]), [
            'name' => 'New Name'
        ]);

        // Verify the response 200,
        // JSON structure
        // Old name is not equal to new name.
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'created_at',
                'updated_at',
                'name',
                'user_id',
                'default',
                'slug',
            ]);

        $this->assertNotEquals($oldName, \App\Schedule::find($schedule->id)->pluck('name'));
    }

    public function testDelete()
    {
        $user = factory(\App\User::class)->create();
        Passport::actingAs($user);
        $schedule = factory(\App\Schedule::class)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->json('DELETE', route('user.schedule.delete', [
            'id' => $schedule->id
        ]));
        $response->assertStatus(200);
    }

    /**
     * @group schedule
     */
    public function testToggleEventTo()
    {
        // Create user
        $user = factory(\App\User::class)->create();
        Passport::actingAs($user);

        // Create schedule
        $schedule = factory(\App\Schedule::class)->create([
            'user_id' => $user->id
        ]);

        $event = factory(\App\Event::class)->create();

        // Add one event to schedule
        $response = $this->json('POST', route('user.schedule.event.toggle', [
            'eventId'    => $event->id,
            'scheduleId' => $schedule->id,
        ]), [
            'eventId'    => $event->id,
            'scheduleId' => $schedule->id,
        ]);

        // @todo for now its the full event.
        // but only checking for event ID
        $response
            ->assertStatus(200)
            ->assertJson([
                'attached' => [
                    'id' => $event->id
                ],
                'detached' => []
            ]);
    }

    /**
     * Get all Events for a given Schedule.
     *
     * @group schedule
     */
    public function testEvents()
    {

        $user = factory(\App\User::class)->create();

        Passport::actingAs($user);

        $schedule = new \App\Schedule;
        $schedule->user()->associate($user);
        $schedule->save();

        $events = factory(\App\Event::class, 5)->create();
        $firstThreeEvents = array_slice($events->toArray(), 0, 3);
        $schedule->events()->toggle(array_pluck($firstThreeEvents, 'id'));

        $response = $this->get(route('user.schedule.events', [
            'id' => $schedule->id
        ]));

        $response
            ->assertStatus(200)
            ->assertJsonStructure([[
                'id',
                'created_at',
                'updated_at',
                'title',
                'venue' => [
                    'city' => [
                        'states' => []
                    ]
                ]
            ]]);
    }
}
