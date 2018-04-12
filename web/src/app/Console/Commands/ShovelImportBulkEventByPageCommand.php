<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Event;

class ShovelImportBulkEventByPageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shovel:import-bulk-event-by-page
                            {--c|count= : The number of items to process}
                            {--d|delete : Delete the import file on successful import.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes contents of the event by page files into the database.';

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
        // Prompt number of files to process
        $requestedCount = $this->option('count');
        $dir = 'public/events/bulk';
        $filepaths = Storage::allFiles($dir);

        $delete = $this->option('delete');

        // Remove .DS_Store
        unset($filepaths[array_search('public/events/bulk/.DS_Store', $filepaths)]);

        // Count number of file available, if number of files to process is greater than
        // amount available use whats available.
        $maxItemsCount = count($filepaths);
        $itemsAvailableCount = ($requestedCount > $maxItemsCount) ? $maxItemsCount : $requestedCount;
        $filesToProcess = array_slice($filepaths, 0, $itemsAvailableCount);

        $eventsAdded = [];
        $deletedFilepaths = [];
        $missingVenueIds = [];
        $savedIds = [];

        foreach ($filesToProcess as $filepath) {
            $this->comment('=========================================================');
            $this->info("Processing file: {$filepath}");
            $daysEvents = json_decode(Storage::get($filepath), true);
            // Must have been an empty file, just delete it.
            if (empty($daysEvents)) {
                Storage::delete($filepath);
                continue;
            }

            $venue = \App\Venue::where('usabmx_id', $daysEvents[0][0]['usabmx_venue_id'])->first();
            if ($venue == null) {
                $this->error("Missing venue for: {$filepath}");
                $missingVenueIds[] = $daysEvents[0][0]['usabmx_venue_id'];
                continue;
            }
            foreach ($daysEvents as $daysEvent) {
                foreach ($daysEvent as $dayEvent) {
                    $this->info("Processing Day: {$dayEvent['start_date']}");
                    $foundEvent = \App\Event::where('usabmx_id', $dayEvent['usabmx_id'])->first();
                    if ($foundEvent == null) {
                        $event = new \App\Event;
                        $event->type                    = $dayEvent['type'] ?? '';
                        $event->fee                     = $dayEvent['fee'];
                        $event->usabmx_id               = $dayEvent['usabmx_id'];
                        $event->start_date              = $dayEvent['start_date'];
                        $event->registration_start_time = $dayEvent['registration_start_time'];
                        $event->registration_end_time   = $dayEvent['registration_end_time'];
                        $event->title                   = $dayEvent['title'];
                        $event->usabmx_track_id         = $dayEvent['usabmx_venue_id'];

                        $event->venue()->associate($venue->id);

                        $saved = $event->save();
                        $this->info("Added: {$saved}");
                        $savedIds[] = $saved;
                    } else {
                        $this->info("Already exists, event id: {$foundEvent->id}, usabmx_id: {$dayEvent['usabmx_id']}");
                    }
                }
            }
            if ($delete) {
                $deletedFilepaths[] = $filepath;
                Storage::delete($filepath);
            }
        }

        // Display
        $missingVenueIdsCount  = count($missingVenueIds);
        $processedCount        = count($savedIds);
        $deletedFilepathsCount = count($deletedFilepaths);
        $stillToProcessCount   = count(array_diff($filepaths, $deletedFilepaths));

        $this->comment('---------------------------------------------------------');
        $this->info("Files requested to process: {$requestedCount}");
        $this->info("Files available to process: {$maxItemsCount}");
        $this->info("Files processed: {$deletedFilepathsCount}");
        $this->info("Max files to process: {$maxItemsCount}");
        $this->info("Venues missing: {$missingVenueIdsCount}");
        $this->info("Events added: {$processedCount}");
        $this->info("Still to process: {$stillToProcessCount}");
    }
}
