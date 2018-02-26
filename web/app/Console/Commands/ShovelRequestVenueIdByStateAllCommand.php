<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ShovelVenueByState as VenueByState;
use Illuminate\Support\Facades\Storage;

class ShovelRequestVenueIdByStateAllCommand extends Command
{
    use \App\ShovelTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shovel:request-all-venue-ids
                            {--s|save : Save the results to disk.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve venue IDs for ALL states.';

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

        $this->info('This will request ALL venues IDs for all states.');
        $continue = $this->choice('Continue?', ['Y','N'], 1);
        if ($continue === 'N') {
            $this->info('Done.');
            return;
        }
        $states = array_keys($this->states());

        $this->line('Starting request...');
        $bar = $this->output->createProgressBar(count($states));
        foreach ($states as $state) {
            $venue        = new VenueByState($state);
            $httpResponse = $venue->getHttpResponse();

            // @TODO log these to disk.
            if ($httpResponse !== 200) {
                $this->error("Error.");
                $this->error("HTTP Response: {$httpResponse}.");
                $this->error("URL: {$venue->url()}.");
                continue;
            }
            $id = $venue->parseVenueId();
            $venuesArray[ $state ] = [
                'url'    => $venue->url(),
                'count'  => count($id),
                'venues' => $id,
            ];
            $bar->advance();
        }
        $bar->finish();

        // Total venues
        $totalVenues = 0;
        foreach ($venuesArray as $venueArray) {
            $totalVenues += count($venueArray['venues']);
        }

        $result = [];
        foreach ($venuesArray as $k => $v) {
            $result = array_merge($result, $v['venues']);
        }

        $this->line("\n");
        $this->info("Total venues: {$totalVenues}.");
        $this->info(
            sprintf(
                "Results: %s.",
                implode(',', $result)
            )
        );

        $save = $this->option('save') ?: $this->choice("Save to disk?", ['Y', 'N'], 1);
        if ($save === 'N') {
            $this->info('Done.');
            return true;
        }

        $filename = str_slug("all venue ids", '-');
        $saved    = Storage::disk('local')->put(
            "public/venues/bulk/{$filename}.json",
            json_encode($result, JSON_FORCE_OBJECT)
        );

        if ($saved === false) {
            $this->error("Failed to save file: {$filename}.");
            return false;
        }
        $this->info('Saved.');
        return true;
    }
}
