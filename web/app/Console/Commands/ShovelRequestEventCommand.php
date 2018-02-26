<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ShovelEvent as ShovelEvent;
use Illuminate\Support\Facades\Storage;

class ShovelRequestEventCommand extends Command
{
    use \App\ShovelTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shovel:request-event-by-id
                            {--i|event_id= : The ID of an event.}
                            {--s|save : Save to disk.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve Event detail based on an Event ID.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $eventIdAsk   = $this->option('event_id') ?? $this->ask("Enter Event ID?");
        $event        = new ShovelEvent($eventIdAsk);
        $httpResponse = $event->getHttpResponse();

        if ($httpResponse !== 200) {
            $this->error("HTTP Response: {$httpResponse}.");
            $this->error("URL: {$event->url()}.");
            $this->error("Event ID: {$eventIdAsk} may not exists. Needs manual inspection.");
            return;
        }

        $title = $event->title();

        $this->info("HTTP Response: {$httpResponse}.");
        $this->info("URL: {$event->url()}.");
        $this->info("Retrieved detail for: {$title}.");

        $result = [
            'title'                   => $title,
            'usabmx_id'               => $event->idFromShareLinks(),
            'usabmx_venue_id'         => $event->venueId(),
            'fee'                     => $event->fee(),
            'type'                    => $event->getTypeFromTitle(),
            'registration_start_time' => $event->registrationStartTime(),
            'registration_end_time'   => $event->registrationEndTime(),

            // National
            'start_date'              => $event->startDate(),
            'end_date'                => $event->endDate(),
            'flyer_uri'               => $event->flyerUri(),
            'event_schedule_uri'      => $event->eventScheduleUri(),
            'hotel_uri'               => $event->hotelUri(),
        ];

        $this->info("\nDetail:");
        foreach ($result as $k => $v) {
            $this->comment("{$k}: {$v}");
        }

        $save = $this->option('save') ?: $this->choice("Save to disk?", ['Y','N'], 1);

        if ($save === "N") {
            $this->info("Done.");
            return;
        }

        $filename = str_slug("{$eventIdAsk} {$title}");
        $filepath = "public/events/detail/{$filename}.json";
        $saved    = Storage::disk('local')->put($filepath, json_encode($result));

        if ($saved === false) {
            $this->error("Failed to save: {$filepath}.");
            return false;
        }

        $this->info("Saved: {$filepath}.");
        return true;
    }
}
