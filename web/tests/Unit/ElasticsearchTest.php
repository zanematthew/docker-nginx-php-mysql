<?php

namespace Tests\Unit;

use Tests\TestCase;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;

use GuzzleHttp\Ring\Client\MockHandler;
use Elasticsearch\ClientBuilder;

class ElasticsearchTest extends TestCase
{
    public function setUP()
    {
        parent::setUp();
        Passport::actingAs(factory(\App\User::class)->create());
        // config(['elasticsearch.defaultConnection' => 'testing']);

        // The connection class requires 'body' to be a file stream handle
        // Depending on what kind of request you do, you may need to set more values here
        $handler = new MockHandler([
          'status' => 200,
          'transfer_stats' => [
             'total_time' => 100
          ],
          'body' => fopen(base_path('tests/Unit/mockelasticsearch.json'), 'r')
        ]);

        $builder = ClientBuilder::create();
        $builder->setHosts(['testing']);
        $builder->setHandler($handler);
        $this->client = $builder->build();
        $response = $this->client->search([
            'index' => 'my_index',
            'type' => 'my_type',
            'body' => [
                [
                  'query' => [
                    'simple_query_string' => [
                      'query' => 'BMX',
                      'fields' => ['name']
                    ]
                  ]
                ]
            ]
        ]);
        // dd($response);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        // $response = $this->get(route('search.venue.suggestion', [
        //     'latlon' => '39.2846225,-76.7605701'
        // ]));

        // dd($response->json());
        // dd(get_class_methods($response));
    }
}
