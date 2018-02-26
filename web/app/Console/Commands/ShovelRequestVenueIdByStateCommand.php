<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ShovelVenueByState as VenueByState;
use Illuminate\Support\Facades\Storage;

class ShovelRequestVenueIdByStateCommand extends Command
{
    use \App\ShovelTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shovel:request-venue-id-by-state
                            {--state= : State Abbreviation.}
                            {--s|save : Save to disk.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve a list of Venue IDs (and names) by state abbreviation.';

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
        $state = $this->option('state') ?? $this->ask("Enter state by abbreviation, i.e., 'MD'");

        $this->line("Sending request...");
        $venue        = new VenueByState($state);
        $httpResponse = $venue->getHttpResponse();

        if ($httpResponse !== 200) {
            $this->error("Error.");
            $this->error("HTTP Response: {$httpResponse}.");
            $this->error("URL: {$venue->url()}.");
            return;
        }

        $id      = $venue->parseVenueId();
        $idCount = count($id);
        $result = [
            'url'    => $venue->url(),
            'venues' => $id,
        ];

        $this->line("Results.");
        $this->line("State: {$state}");
        $this->line("Count: {$idCount}");
        $this->line("URL: {$result['url']}");
        $this->line(sprintf('ID(s) received: %s', implode(',', $id)));

        $save = $this->option('save') ?: $this->choice("Save to disk?", ['Y', 'N'], 1);
        if ($save === "N") {
            $this->info('Done.');
            return;
        }

        $dir = 'public/venues/bulk';
        $file = sprintf(
            '%s/%s.json',
            $dir,
            str_slug("{$state} venue ids", '-')
        );
        Storage::disk('local')->put(
            $file,
            json_encode($id, JSON_FORCE_OBJECT)
        );

        $this->info("Saved: {$dir}/{$file}.");
        return true;
    }
}
