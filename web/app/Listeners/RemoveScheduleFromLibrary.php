<?php

namespace App\Listeners;

use App\Library;
use App\Events\BeforeScheduleDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveScheduleFromLibrary
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
        $libraryItem = Library::where([
            'item_id' => $event->schedule->id,
            'user_id' => \Auth::id(),
        ]);
        $libraryItem->delete();
    }
}
