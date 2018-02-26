<?php

namespace App\Listeners;

use App\Events\BeforeScheduleDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveEventsFromSchedule
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  BeforeScheduleDeleted  $event
     * @return void
     */
    public function handle(BeforeScheduleDeleted $event)
    {
        $event->schedule->events()->detach();
    }
}
