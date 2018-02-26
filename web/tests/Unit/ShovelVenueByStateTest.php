<?php

namespace Tests\Unit;

use Tests\TestCase;
use Goutte\Client as GoutteClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use App\ShovelVenueByState;

class ShovelVenueByStateTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        // @TODO Move this into its own custom parent setUp(); ?
        $venueHtml = file_get_contents(base_path('tests/Unit/data/bulk-venue.html'));
        $mock      = new MockHandler([
            new Response(404, ['Content-type' => 'text/html'], $venueHtml),
        ]);

        $httpClient = new GuzzleClient([
            'handler' => HandlerStack::create($mock),
        ]);

        $goutte = new GoutteClient();
        $goutte->setClient($httpClient);

        $venueIdByStateAbbr = new ShovelVenueByState('MD');
        $venueIdByStateAbbr->setClient($goutte);
        $this->venueIdByStateAbbr = $venueIdByStateAbbr;
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testParseVenueIdsByStateAbbr()
    {
        $this->assertArraySubset([
            572,
            410,
            589,
            359,
        ], $this->venueIdByStateAbbr->parseVenueId());
    }

    public function testUrl()
    {
        $this->assertEquals(
            'http://usabmx.com/site/bmx_tracks/by_state?section_id=12&state=MD',
            $this->venueIdByStateAbbr->url()
        );
    }
}
