<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use \App as App;

class DatabaseTest extends TestCase
{

    use DatabaseMigrations;
    use DatabaseTransactions;

    /**
     * This test will create 5 Events, add those 5 Events to a schedule,
     * and verify the schedule has 5 Events.
     *
     * If this test passes, we can presume that;
     * A User was created.
     * A Schedule was created, and assigned to a User,
     * A Event was created, and assigned to a Venue,
     * A Venue was created, and assigned to a City,
     * A City was created, and assigned to a State,
     * a State was created.
     *
     * @return void
     */
    public function testAssignEventsToSchedule()
    {
        $expected = 5;

        $schedule_id = factory(App\Schedule::class)->create()->id;
        $event_ids   = factory(App\Event::class, $expected)->create()->pluck('id');
        $synced      = App\Schedule::find($schedule_id)->events()->sync($event_ids);

        $actual = count($synced['attached']);

        $this->assertEquals($expected, $actual);
    }
}
