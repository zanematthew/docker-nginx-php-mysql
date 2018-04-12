<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ShovelRequestDetailBulkCommand extends Command
{
    use \App\ShovelTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shovel:request-detail-bulk
                            {--t|type= : Type to request detail for [venue|event].}
                            {--c|count= : Amount of IDs to process.}
                            {--f|file= : File name to retrieve list of IDs from.}
                            {--s|save : Save requested content to disk.}
                            {--d|delete_source : Delete source bulk ID file.}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Request [venue|event] detail for previously saved [venue|event] IDs.';

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
        $requestedType = $this->option('type') ?? $this->choice('Type?', ['venue', 'event']);

        if ($requestedType == 'venue') {
            $dir = 'venues';
        } elseif ($requestedType == 'event') {
            $dir = 'events';
        } else {
            $this->error("Invalid type: {$requestedType}");
            return false;
        }

        $bulkDir = "public/{$dir}/bulk/";

        // Handle venues OR events
        $bulkIdsJsonfile = array_values(
            array_filter(
                array_map(
                    function ($dirFile) {
                        if (str_contains($dirFile, '.json')) {
                            return $dirFile;
                        }
                    },
                    Storage::files($bulkDir)
                )
            )
        );

        // Prefix with "all"
        $validOptions  = array_merge([0 => 'all'], $bulkIdsJsonfile);
        $filesToProcess = $this->option('file') ?? $this->choice('Select a file to process?', $validOptions);

        $requestedCount = $this->option('count') ?? $this->ask('Number of IDs to request detail for?');
        if (is_numeric($requestedCount) === false) {
            $this->error("Not a number: {$requestedCount}.");
            return false;
        }

        if ($filesToProcess === 'all') {
            $filesToProcess = $bulkIdsJsonfile;
        }


        foreach ($filesToProcess as $fileToProcess) {
            $contents      = json_decode(Storage::get($fileToProcess), true);
            $contentsCount = count($contents);
            $maxCount      = $requestedCount > $contentsCount ? $contentsCount : $requestedCount;

            // array rand returns an int when only one value is found.
            $randomIdKeys = (array) array_rand($contents, $maxCount);
            foreach ($randomIdKeys as $randomIdkey) {
                $randomIdsToProcess[] = $contents[ $randomIdkey ];
            }
            $this->comment("Processing: {$requestedCount} random ID(s) from: {$fileToProcess}.");

            // For each random ID request detail
            $failedIdsToProcess = [];
            foreach ($randomIdsToProcess as $randomIdToProcess) {
                if ($requestedType == 'venue') {
                    $cmd    = 'shovel:request-venue-by-id';
                    $params = [
                        '--venue_id' => $randomIdToProcess,
                        '--save'     => true,
                    ];
                }

                if ($requestedType == 'event') {
                    $cmd = 'shovel:request-event-detail-by-id';
                    $params = [
                        '--event_id' => $randomIdToProcess,
                        '--save'     => true,
                    ];
                }

                $exitCode = $this->call($cmd, $params);
                if ($exitCode === false) {
                    $failedIdsToProcess[] = $randomIdToProcess;
                    continue;
                }
                $processedIds[] = $randomIdToProcess;
            }

            $stillToProcessIds = array_diff($contents, $processedIds);

            if (empty($stillToProcessIds)) {
                $this->info("All IDs are now processed. In {$fileToProcess}");
                $toDelete = $this->option('delete_source') ?: $this->choice('Delete bulk id file', ['Y', 'N'], 1);
                if ($toDelete === true || $toDelete === 'Y') {
                    Storage::delete($fileToProcess);
                    $this->info("Removed file: {$fileToProcess}");
                }
            }

            $fileInfo = pathinfo(basename($fileToProcess));
            $result   = array_filter(array_values($stillToProcessIds));
            $filename = $fileInfo['filename'];
            // venues OR events
            $saved    = Storage::disk('local')->put(
                "public/{$dir}/bulk/{$filename}.json",
                json_encode($result, JSON_FORCE_OBJECT)
            );
            if ($saved === false) {
                $this->error("Failed to save file: {$filename}.");
                return false;
            }

            $this->info("ID(s) processed: {$requestedCount}.");
            $this->info(sprintf('ID(s) remaining: %s.', count($stillToProcessIds)));
        }
    }
}
