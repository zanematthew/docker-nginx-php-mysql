<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\ShovelEventsSchedule;

class ShovelRequestEventsByPageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shovel:request-events-by-page
                            {--venue_id= : Venue id.}
                            {--p|page_id= : Venue page id.}
                            {--m|month= : Month, i.e., 01, 12}
                            {--y|year= : Year}
                            {--s|save : Save to disk.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        //
        // -m 06 -venue_id 359 -page_id 1623
        $venuePageId = $this->option('page_id') ?? $this->ask('Page ID?');
        $venueId     = $this->option('venue_id') ?? $this->ask('Venue ID?');
        $year        = $this->option('year') ?? $this->ask('Year?');
        $month       = $this->option('month') ?? $this->ask('Month?');

        $result = new ShovelEventsSchedule($venuePageId, $venueId, $year, $month);
        $httpResponse = $result->getHttpResponse();

        if ($httpResponse !== 200) {
            $this->error("HTTP Response: {$httpResponse}.");
            $this->error("URL: {$result->buildUrl()}.");
            return 0;
        }

        $this->info('==');
        $this->info('URL: ' . $result->buildUrl());
        $this->info('Count: ' . $result->count());

        $save = $this->option('save') ?: $this->choice("Save to disk?", ['Y','N'], 1);
        if ($save === "N") {
            $this->info("Done.");
            return 1;
        }

        $events = $result->events();
        if (empty($events)){
            $this->info('No events');
            return 0;
        }
        $filename = str_slug("{$venueId} {$venuePageId} {$month} {$year}");
        $saved    = Storage::disk('local')->put("public/events/bulk/{$filename}.json", json_encode($result->events()));
        if ($saved === false) {
            $this->error("Failed to save: {$filename}.");
            return 0;
        }
        return 1;
    }
}
