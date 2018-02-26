<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Storage;

class ShovleParseVenuePageFromDetailIdCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shovel:page-id-from-venue-detail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'From Venue detail determine the page ID.';

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
        $bulkDir = 'public/venues/detail/';
        $dirFiles = Storage::files($bulkDir);
        unset($dirFiles[array_search('public/venues/detail/.DS_Store', $dirFiles)]);

        foreach ($dirFiles as $dirFile) {
            $file = Storage::get($dirFile);
            $venue = json_decode($file);
            preg_match('/tracks\/(.*?)\/events/', $venue->scheduleUri, $id);
            $ids[] = [
                'page_id'  => $id[1],
                'venue_id' => $venue->id,
            ];
        }
        
        $saved = Storage::put('public/venues/bulk/all-venue-page-ids.json', json_encode($ids, JSON_FORCE_OBJECT));
        dd($saved);
    }
}
