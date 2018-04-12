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
    protected $signature = 'elasticsearch:install
                            {--destroy : Remove the index if it exists.}';

    /**
     * The console command description.
     * Detail see; https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping.html';
     *
     * @var string
     */
    protected $description = 'Install an index pattern, based on the .env value.';

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
        $index = config('elasticsearch.indexParams')['index'];
        $this->info(sprintf('Creating index pattern based on value from .env: %s', $index));

        if (Elasticsearch::indices()->exists(['index' => $index])) {
            $destroyIndex = $this->option('destroy') ?: $this->choice(
                'Index exists. Destroy it, and create a new one?',
                ['Yes', 'No'],
                1
            );
            if ($destroyIndex === 'No') {
                $this->info('Exiting.');
                return;
            }
            $return = Elasticsearch::indices()->delete(['index' => $index]);
            if ($return['acknowledged']) {
                $this->info(sprintf('Destroying index %s', $index));
            } else {
                $this->error('Error destroying index:'.print_r($return, true));
                exit;
            }
        }

        $return = Elasticsearch::indices()->create([
            'index' => $index,
            'body'  => config('elasticsearch.indexParams')['body'],
        ]);

        if ($return['acknowledged']) {
            $this->info(sprintf('Created %s', $index));
        } else {
            $this->error('Error:'.print_r($return, true));
        }
    }
}
