<?php

namespace Tests\Unit;

use Tests\TestCase;
use Goutte\Client as GoutteClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use App\ShovelVenue;

class ShovelVenueEmptyNodesTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $venueHtml = file_get_contents(base_path('tests/Unit/data/single-venue-empty-nodes.html'));
        $mock      = new MockHandler([
            new Response(200, ['Content-type' => 'text/html'], $venueHtml),
        ]);

        $httpClient = new GuzzleClient([
            'handler' => HandlerStack::create($mock),
        ]);

        $goutte = new GoutteClient();
        $goutte->setClient($httpClient);

        $venue = new ShovelVenue(123);
        $venue->setClient($goutte);
        $this->venue = $venue;
    }

    public function testContact()
    {
        $this->assertEmpty($this->venue->contact());
    }

    public function testParseLogo()
    {
        $this->assertEquals('', $this->venue->parseLogo());
    }

    public function testParseDistrict()
    {
        $this->assertEquals('', $this->venue->parseDistrict());
    }

    public function testParseLinksUri()
    {
        $schedule = $this->venue->parseScheduleUri();
        $map      = $this->venue->parseMapUri();
        $website  = $this->venue->parseWebsiteUri();

        $this->assertEquals('', $schedule);
        $this->assertEquals('', $map);
        $this->assertEquals('', $website);
    }

    public function testParseLatLongFromMapsUri()
    {
        $this->assertEmpty('', $this->venue->parseLatLongMapsUri());
    }
}
