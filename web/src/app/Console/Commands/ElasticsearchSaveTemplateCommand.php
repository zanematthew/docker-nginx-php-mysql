<?php

namespace App\Console\Commands;

use Elasticsearch;
use Illuminate\Console\Command;

class ElasticsearchSaveTemplateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:installTemplates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the templates listed in this file.';

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
        $templates = new \App\ElasticsearchSearchTemplates;
        // @todo add update/status messages
        Elasticsearch::putScript($templates->eventDate());
        Elasticsearch::putScript($templates->eventPhrase());
        Elasticsearch::putScript($templates->eventSuggest());
        Elasticsearch::putScript($templates->venuePhrase());
        Elasticsearch::putScript($templates->venueSuggest());
    }
}
