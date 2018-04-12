<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ShovelImportBulkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shovel:import-bulk
                            {--t|type= : The type to process.}
                            {--c|count= : The number of items to process.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk import from a directory.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function buildBulkDir($requestedType = null): string
    {
        if ($requestedType == 'venue') {
            $dir = 'venues';
        } elseif ($requestedType == 'event') {
            $dir = 'events';
        } else {
            return '';
        }
        return "public/{$dir}/detail/";
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Prompt for type.
        $requestedType = $this->option('type') ?? $this->choice('Type?', ['venue', 'event']);

        $detailDir = $this->buildBulkDir($requestedType);
        if (empty($detailDir)) {
            $this->error("Invalid type: {$requestedType}");
            return 0;
        }

        // Verify contents exists in directory.
        $tmpFiles = Storage::allFiles($detailDir);

        // Remove any non-JSON files.
        $files = [];
        foreach ($tmpFiles as $file) {
            if (str_contains($file, '.json')) {
                $files[] = $file;
            }
        }

        $fileCount = count($files);
        if ($fileCount == 0) {
            $this->info('No files to import.');
            return 0;
        }
        $this->info("Files found: {$fileCount}.");

        // Prompt for number of files to import.
        $requestedCount = $this->option('count') ?? $this->ask('Number of files to import?');
        if (is_numeric($requestedCount) === false) {
            $this->error("Not a number: {$requestedCount}.");
            return false;
        }

        // Import file (run cmd).
        $i = 0;
        $failures = [];
        while ($i < $fileCount) {
            $cmd = "shovel:import-detail";
            $params = [
                '--type'        => $requestedType,
                '--file_path'   => $files[ $i ],
                '--remove_file' => 'N',
                '--overwrite'   => 'Y',
            ];
            $exitCode = $this->call($cmd, $params);
            if ($exitCode === 0) {
                $failures[] = $files[ $i ];
            }
            if ($i == $requestedCount) {
                break;
            }
            $i++;
        }

        $failuresCount = count($failures);
        $stillToImport = ($fileCount - $i) + $failuresCount;

        $this->info("File count: {$fileCount}.");
        $this->info("Imported: {$i}.");
        $this->info("Requested: {$requestedCount}.");
        $this->info("Pending: {$stillToImport}.");
        $this->error("Failures: {$failuresCount}.");
        $this->info('Done.');
    }
}
