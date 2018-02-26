<?php

namespace App\Console\Commands;

use Elasticsearch;
use Illuminate\Console\Command;

class ElasticsearchCreateIndexMapping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:install
                            {name : The name of the index to create.}
                            {--destroy : Remove the index if it exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install an index pattern. Detail see; https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping.html';

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
        $name = $this->argument('name');

        $params['index'] = $name;

        if (Elasticsearch::indices()->exists($params)) {
            $destroyIndex = $this->option('destroy') ?: $this->choice('Index exists. Destroy it, and create a new one?', ['Yes', 'No'], 1);
            if ($destroyIndex === 'No'){
                $this->info('Exiting.');
                return;
            }
            $return = Elasticsearch::indices()->delete($params);
            if ($return['acknowledged']) {
                $this->info('Destroyed.');
            } else {
                $this->error('Error destroying index:'.print_r($return, true));
                exit;
            }
        }

        $params['body'] = config('elasticsearch.indexParams')['body'];
        $return = Elasticsearch::indices()->create($params);
        if ($return['acknowledged']) {
            $this->info('Created.');
        } else {
            $this->error('Error:'.print_r($return, true));
        }
    }
}
