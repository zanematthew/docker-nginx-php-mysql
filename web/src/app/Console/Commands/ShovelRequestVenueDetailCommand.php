<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ShovelVenue as VenueDetail;
use Illuminate\Support\Facades\Storage;

class ShovelRequestVenueDetailCommand extends Command
{
    use \App\ShovelTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shovel:request-venue-by-id
                            {--i|venue_id= : The ID of a venue.}
                            {--s|save : Save to disk.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'You give me venue ID, I give you venue detail.';

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

        $venueId      = $this->option('venue_id') ?? $this->ask("Enter Venue ID?");
        $venue        = new VenueDetail($venueId);
        $httpResponse = $venue->getHttpResponse();

        if ($httpResponse !== 200) {
            $this->error("HTTP Response: {$httpResponse}.");
            $this->error("URL: {$venue->url()}.");
            $this->error("Venue ID: {$venueId} may not exists. Needs manual inspection.");
            return false;
        }

        $name = $venue->parseName();

        $this->info("===========================================================================================");
        $this->info("HTTP Response: {$httpResponse}.");
        $this->info("URL: {$venue->url()}.");
        $this->info("===========================================================================================");

        $detail = [
            'id'          => $venueId,
            'name'        => $name,
            'description' => $venue->parseDescription(),
            'district'    => $venue->parseDistrict()
        ];

        $contact = [];
        foreach ($venue->contact() as $key => $value) {
            $contact[ $key ] = $value;
        }

        $location = [
            'street'    => $venue->getStreet(),
            'city'      => $venue->getCity(),
            'stateAbbr' => $venue->getStateAbbr(),
            'zipCode'   => $venue->getZipCode(),
            'country'   => $venue->getCountry(),
            'lat'       => $venue->parseLatLongMapsUri()['lat'],
            'long'      => $venue->parseLatLongMapsUri()['long'],
        ];

        $links = [
            'scheduleUri' => $venue->parseScheduleUri(),
            'mapUri'      => $venue->parseMapUri(),
            'websiteUri'  => $venue->parseWebsiteUri(),
            'logoUri'     => $venue->parseLogo(),
        ];

        $result = array_merge($detail, $location, $links, $contact);
        foreach ($result as $k => $v) {
            $this->comment("{$k}: {$v}");
        }

        $save = $this->option('save') ?: $this->choice("Save to disk?", ['Y','N'], 1);

        if ($save === "N") {
            $this->info("Done.");
            return true;
        }

        $result   = array_merge($detail, $location, $links, $contact);
        $filename = str_slug("{$venueId} {$name}", '-');
        $filepath = "public/venues/detail/{$filename}.json";
        $saved    = Storage::disk('local')->put($filepath, json_encode($result));

        if ($saved === false) {
            $this->error("Failed to save: {$filepath}.");
            return false;
        }

        $this->info("Saved: {$filepath}.");
        return true;
    }
}
