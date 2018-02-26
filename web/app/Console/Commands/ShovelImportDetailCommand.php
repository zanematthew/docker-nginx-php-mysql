<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Venue;
use App\City;
use App\State;
use App\CityState;
use App\Event;

class ShovelImportDetailCommand extends Command
{
    use \App\ShovelTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shovel:import-detail
                            {--t|type= : The requested type [venue|event].}
                            {--f|file_path= : The file path relative to the Storage directory, i.e., public/venues/detail/154-river-valley-bmx.json.}
                            {--s|save : Save to the database.}
                            {--r|remove_file : Remove import from file when done.}
                            {--o|overwrite : If the venue|event already exists, overwrite the existing item.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports a Venue or Event into the database based on the full file path.';

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
        // ask for file
        $filePath = $this->option('file_path') ?? $this->ask("Please enter file path");

        // verify file
        $exists = Storage::disk('local')->exists($filePath);
        if ($exists === false) {
            $this->error("File not found: {$filePath}.");
            return 0;
        }

        // read contents of file
        $contents = Storage::get($filePath);

        // display contents of file
        $contentsArray = json_decode($contents, true);
        if (empty($contentsArray)) {
            $this->error("Empty file: {$filePath}.");
            return 0;
        }

        // Display info
        $this->info("Ready to import.");
        foreach ($contentsArray as $k => $v) {
            $this->comment("{$k}: {$v}");
        }

        $requestedType = $this->option('type') ?? $this->choice('Import type', ['venue', 'event']);

        if ($requestedType == 'venue') {
            $imported = $this->importVenue($contentsArray);
        } elseif ($requestedType == 'event') {
            $imported = $this->importEvent($contentsArray);
        } else {
            $imported = [
                'message'  => "Invalid requested type: {$requestedType}",
                'exitCode' => 0
            ];
        }

        if ($imported['exitCode'] === 0) {
            $this->info("{$imported['message']}.");
            return 0;
        }

        $this->info('Saved.');
        $remove_file = $this->option('remove_file') ?: $this->choice('Remove file?', ['Y', 'N'], 1);
        if ($remove_file === 'N') {
            $this->info('Done.');
            return 1;
        }

        // Handle delete.
        $deleted = Storage::delete($filePath);
        if ($deleted === false) {
            $this->error("Failed to delete: {$filePath}.");
            return 0;
        }

        $this->info('Deleted file.');
        return 1;
    }

    // [
    //   message => str
    //   exitCode => 0|1
    // ]
    public function importVenue($contentsArray = null): array
    {
        $venue = \App\Venue::where('name', $contentsArray['name'])->first();

        // Handle overwrite
        if ($venue) {
            $overwrite = $this->option('overwrite') ?: $this->choice("Venue {$venue->name} ({$venue->id}) exists, overwrite?", ['Y', 'N'], 1);
            if ($overwrite === 'N') {
                return [
                    'message'  => 'Done',
                    'exitCode' => 0,
                ];
            }
        } else {
            $venue = new Venue;
        }

        // Venue has State
        if (empty($contentsArray['stateAbbr'])) {
            return [
                'message'  => 'Missing state.',
                'exitCode' => 0,
            ];
        }

        if ($this->isStateValid($contentsArray['stateAbbr']) === false) {
            return [
                'message'  => "State invalid: {$contentsArray['stateAbbr']}",
                'exitCode' => 0,
            ];
        }

        // Create state if not exists.
        $state = \App\State::where('abbr', $contentsArray['stateAbbr'])->first();
        if ($state === null) {
            $this->comment("State does not exists, creating state...");
            $state = new State;
            $state->name = $this->states()[ $contentsArray['stateAbbr'] ];
            $state->abbr = $contentsArray['stateAbbr'];
            $state->save();
            $this->info("State ID: {$state->id}.");
        }

        // Create city if not exists.
        $city = \App\City::where('name', $contentsArray['city'])->first();
        if ($city === null) {
            $this->comment("City does not exists, creating city...");
            $city = new City;
            $city->name = $contentsArray['city'];
            $city->save();
            $this->info("City ID: {$city->id}.");
        }

        // Link CityState.
        $state->cities()->syncWithoutDetaching([ $city->id ]);
        $state->save();

        // Assign venue.
        $venue->name            = $contentsArray['name'];
        $venue->district        = $contentsArray['district'] ?? null;
        $venue->usabmx_id       = $contentsArray['id'];
        $venue->website         = $contentsArray['websiteUri'] ?? null;
        $venue->image_uri       = $contentsArray['logoUri'] ?? null;
        $venue->description     = $contentsArray['description'] ?? null;
        $venue->street_address  = $contentsArray['street'] ?? null;
        $venue->zip_code        = $contentsArray['zipCode'] ?? null;
        $venue->lat             = $contentsArray['lat'] ?? null;
        $venue->long            = $contentsArray['long'] ?? null;
        $venue->email           = $contentsArray['email'] ?? null;
        $venue->primary_contact = $contentsArray['primaryContact'] ?? null;
        $venue->phone_number    = $contentsArray['phone'] ?? null;

        // Associate city.
        $venue->city()->associate($city->id);

        // Update DB record.
        $saved = $venue->save();

        // $venue->country = $contentsArray['country'];
        // $venue->scheduleUri = $contentsArray['scheduleUri'];
        // $venue->mapUri = $contentsArray['mapUri'];

        // Handle saved.
        if ($saved === false) {
            return [
                'message'  => "Failed to save: {$venue->name}.",
                'exitCode' => 0,
            ];
        }
        return [
            'message'  => 'Imported',
            'exitCode' => 1
        ];
    }

    public function importEvent($contentsArray = null): array
    {
        // Check if Event exists
        $event = \App\Event::where('usabmx_id', $contentsArray['usabmx_id'])->first();
        if ($event) {
            $overwrite = $this->option('overwrite') ?: $this->choice("Event {$event->title} ({$event->id}) exists, overwrite?", ['Y', 'N'], 1);
            if ($overwrite === 'N') {
                return [
                    'message'  => 'Done',
                    'exitCode' => 0,
                ];
            }
        } else {
            $event = new Event;
        }

        // Check if Venue exists (by USABMX ID)
        $venue = \App\Venue::where('usabmx_id', $contentsArray['usabmx_venue_id'])->first();
        if (empty($venue)) {
            return [
                'message' => 'Venue does not exists',
                'exitCode' => 0
            ];
        }

        // Build event
        $event->title                   = $contentsArray['title'];
        $event->usabmx_id               = $contentsArray['usabmx_id'];
        $event->usabmx_track_id         = $contentsArray['usabmx_venue_id'];
        $event->fee                     = $contentsArray['fee'] ?? '';
        $event->type                    = str_slug($contentsArray['type']) ?? '';
        $event->registration_start_time = date('H:i:s', strtotime($contentsArray['registration_start_time'])) ?? '';
        $event->registration_end_time   = date('H:i:s', strtotime($contentsArray['registration_end_time'])) ?? '';
        $event->start_date              = date('Y-m-d', strtotime($contentsArray['start_date'])) ?? '';
        $event->end_date                = date('Y-m-d', strtotime($contentsArray['end_date'])) ?? '';
        $event->flyer_uri               = $contentsArray['flyer_uri'] ?? '';
        $event->event_schedule_uri      = $contentsArray['event_schedule_uri'] ?? '';
        $event->hotel_uri               = $contentsArray['hotel_uri'] ?? '';

        $event->venue()->associate($venue->id);
        $saved = $event->save();

        // Handle saved.
        if ($saved === false) {
            return [
                'message'  => "Failed to save: {$event->title}.",
                'exitCode' => 0,
            ];
        }
        return [
            'message'  => 'Imported',
            'exitCode' => 1
        ];
    }
}
