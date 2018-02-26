<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ShovelRequestBulkEventByPageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shovel:request-event-bulk-by-page
                            {--m|month_range= : The month range; 1-12}
                            {--c|count= : The number of items to process.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Request ALL events for a given month, spanning a month range.';

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
        $startTime = strtotime('now');

        // Ask for month range
        $monthRange = $this->option('month_range');

        // Parse page range
        if (str_contains($monthRange, '-')) {
            list($startMonth, $endMonth) = explode('-', $monthRange);
        } else {
            $startMonth = $endMonth = $monthRange;
        }
        if ($startMonth < 1 && $startMonth > 12 || $endMonth < 1 && $endMonth){
            $this->error("Invalid month range: {$monthRange}.");
            return false;
        }

        // Number of items to get from file.
        $requestedCount = $this->option('count');
        $filepathAllVenueScheduleIds = 'public/venues/bulk/all-venue-schedule-ids.json';
        $contents = json_decode(Storage::get($filepathAllVenueScheduleIds), true);
        $maxItems = count($contents);
        $itemsAvailableCount = ($requestedCount > $maxItems) ? $maxItems : $requestedCount;

        // Get first chunk of items from array based on "count".
        $contentsToProcess = array_slice($contents, 0, $itemsAvailableCount);
        $i = 1;
        foreach ($contentsToProcess as $content) {
            $this->line('----------------------------------------------------------------------------------');
            $this->comment("Processing ({$i} of {$itemsAvailableCount}), Venue ID: {$content['venue_id']}, Page ID: {$content['page_id']}");
            $startMonthIterator = $startMonth;
            while ($startMonthIterator <= $endMonth) {
                $exitCode = $this->call('shovel:request-events-by-page', [
                    '--venue_id' => $content['venue_id'],
                    '--page_id'  => $content['page_id'],
                    '--month'    => $startMonthIterator,
                    '--year'     => 2017,
                    '--save'     => true,
                ]);
                $exitCode = true;
                $this->comment("Start month: {$startMonthIterator} end month: {$endMonth}");
                if ($exitCode == 1) {
                    $itemsProcessed[] = $content;
                }
                $startMonthIterator++;
            }
            $i++;
        }

        $contentsStillToProcess = array_values(array_udiff($contents, $contentsToProcess, function($a, $b){ return $a['page_id'] - $b['page_id'];}));
        $contentsStillToProcessCount = count($contentsStillToProcess);
        $itemsProcessedCount = count($itemsProcessed);

        // Update file, with items processed.
        Storage::disk('local')->put($filepathAllVenueScheduleIds, json_encode($contentsStillToProcess, true));
        $endTime = strtotime('now');

        // Display
        $this->info("Number of items to process: {$maxItems}");
        $this->info("Number of items requested to process: {$requestedCount}");
        $this->info("Number of items available to process: {$itemsAvailableCount}");
        $this->info("Number of items processed: {$itemsProcessedCount}");
        $this->info("Number of items left to process: {$contentsStillToProcessCount}");
        $this->info(sprintf('Start Time: %s, End Time: %s, Duration: %s',
            date('H:i:s', $startTime),
            date('H:i:s', $endTime),
            gmdate('i:s', $endTime - $startTime)
        ));
        $this->line("----------------------------------------------------------------");

    }
}
