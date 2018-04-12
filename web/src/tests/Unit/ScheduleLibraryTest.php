<?php

namespace Tests\Unit;

use Tests\TestCase;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleLibraryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     * @group schedule-library
     * @test
     */
    public function when_schedule_deleted_remove_from_user_library()
    {
        $user = factory(\App\User::class)->create();
        Passport::actingAs($user);

        // Create schedule
        $schedule = factory(\App\Schedule::class)->create([
            'user_id' => $user->id,
        ]);

        // Add schedule to library
        $response = $this->post(route('library.toggle.item', [
            'item_id'   => $schedule->id,
            'item_type' => 'schedule',
        ]), [
            'item_id'   => $schedule->id,
            'item_type' => 'schedule',
        ]);
        $response->assertStatus(200);

        // Delete schedule from library
        $response = $this->json('DELETE', route('user.schedule.delete', [
            'id' => $schedule->id
        ]));
        $response->assertStatus(200);

        // Request all library items, this should be empty if
        // event BeforeScheduleDeleted was called correctly
        // this should be empty.
        $response = $this->get(route('library.get.items'));
        $response->assertStatus(200);

        $this->assertEmpty($response->getData());

    }
}
