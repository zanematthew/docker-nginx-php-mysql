<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ShovelEventIdByType as EventIdByType;
use Illuminate\Support\Facades\Storage;

class ShovelRequestEventIdByTypeCommand extends Command
{
    use \App\ShovelTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shovel:request-event-id-by-type
                            {--t|type= : The type of event. [National, Earned Double, Gold Cup, Race for Life, State]}
                            {--y|year= : The year of event.}
                            {--p|page_range= : The page range.}
                            {--s|save : Save the results to disk.}
                            {--past= : Set this to true to request past events for the current year.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve ALL event IDs based on the allowed options.';

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
        // Handle input.
        $type = $this->option('type') ?? $this->choice('Event Type?', array_keys($this->eventTypes), 0);

        if ($this->isTypeValid($type) === false) {
            $this->error("Invalid type: {$type}.");
            return false;
        }

        $year = $this->option('year') ?? $this->choice('Year?', $this->validYears(), 0);
        if ($this->isYearValid($year) === false) {
            $this->error("Invalid year: {$year}.");
            return false;
        }

        $pageRange = $this->option('page_range') ?? $this->ask('Page range? [1, 1-5, 5-10, etc.]');
        list($startPage, $endPage) = $this->parsedPageRange($pageRange);

        $pastOnly = "false";
        if ($year == date('Y')) {
            $pastOnly = $this->option('past') ?? $this->choice(
                'Request past ONLY events for the current Year?',
                ['Y','N'],
                1
            );
            $this->comment("Include past events for the current year Page: {$pastOnly}.");
        }

        $pastOnlyFix = ($pastOnly === "true") ? true : false;

        $this->comment("Type: {$type}.");
        $this->comment("Year: {$year}.");
        $this->comment("Start Page: {$startPage}.");
        $this->comment("End Page: {$endPage}.");
        $this->comment("Requesting Event IDs for...");

        // Handle Request.
        $results = [
            'year'       => $year,
            'page_range' => $pageRange,
        ];

        $bar            = $this->output->createProgressBar($endPage);
        $didBarFinished = true;
        $initialPage    = $startPage;

        while ($startPage <= $endPage) {
            $eventIdByType    = new EventIdByType($type, $year, $startPage, $pastOnlyFix);
            $eventIdByTypeUrl = $eventIdByType->url($type, $year, $startPage, $pastOnlyFix);
            $httpResponse     = $eventIdByType->getHttpResponse();
            $maxPage          = $eventIdByType->maxPage();

            if ($httpResponse !== 200) {
                $this->error("Error.");
                $this->error("HTTP Response: {$httpResponse}.");
                $this->error("URL: {$eventIdByTypeUrl}.");
            }

            $ids = $eventIdByType->eventIds();
            $events[] = [
                'url'      => $eventIdByTypeUrl,
                'page'     => $startPage,
                'count'    => count($ids),
                'ids'      => $ids,
            ];

            $results = array_merge($results, [
                'max_page' => $maxPage,
                'events'   => $events
            ]);

            // maybe doWhile(?)
            // Check the current page against the actual max page
            // number in the pager.
            $bar->advance();
            if ($startPage >= $maxPage) {
                $bar->finish();
                $this->error("\nMAX PAGE ({$maxPage}) REACHED! Exited early.");
                $didBarFinished = true;
                break;
            }
            $startPage++;
        }

        if ($didBarFinished !== true) {
            $bar->finish();
        }

        // Display results.
        // Total IDs
        $totalIds = 0;
        foreach ($results['events'] as $event) {
            $totalIds += count($event['ids']);
        }

        $this->line("\n");
        $this->info("Max Page available: {$maxPage}");
        $this->info("Total Event IDs: {$totalIds}");
        $this->line("\n");
        foreach ($results['events'] as $eventInfo) {
            $this->info("Page: {$eventInfo['page']}");
            $this->info("URL: {$eventInfo['url']}");
            $this->info(sprintf(
                "Event IDs (%d):\n%s",
                $eventInfo['count'],
                implode(',', $eventInfo['ids'])
            ));
            $this->line("\n");
        }

        // Prompt to save results.
        $save = $this->option('save') ?: $this->choice("Save to disk?", ['Y', 'N'], 1);
        if ($save === "N") {
            $this->info('Done.');
            return true;
        }

        // @TODO should only be IDs
        $result = [];
        foreach ($results['events'] as $eventInfo) {
            $result = array_merge($result, $eventInfo['ids']);
        }

        $pastOnlyText  = $pastOnly === "true" ? 'past' : '';
        $pageRangeText = str_replace('-', '-to-', $pageRange) ?? $initialPage;
        $filename      = str_slug("{$year} {$pastOnlyText} {$type} page {$pageRangeText} of {$maxPage} event ids", '-');
        $saved         = Storage::disk('local')->put(
            "public/events/bulk/{$filename}.json",
            json_encode($result, JSON_FORCE_OBJECT)
        );

        if ($saved === false) {
            $this->error("Failed to save file: {$filename}.");
            return false;
        }
        $this->info("Filename (JSON): {$filename}.");
        $this->info('Saved.');
        return true;
    }
}
