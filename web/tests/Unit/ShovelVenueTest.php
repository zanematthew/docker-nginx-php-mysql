<?php

namespace Tests\Unit;

use Tests\TestCase;

use Goutte\Client as GoutteClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use App\ShovelVenue;

class ShovelVenueTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $venueHtml = file_get_contents(base_path('tests/Unit/data/single-venue.html'));
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

    public function testHttpOkResonse()
    {
        $this->assertEquals(200, $this->venue->getHttpResponse());
    }

    public function testContact()
    {
        $this->assertArraySubset([
            'email'            => 'sinchakelectric@verizon.net',
            'phone'            => '(410) 969-5177',
            'primaryContact'   => 'Tom Sinchak ((410) 977 2963)',
            'secondaryContact' => 'Charles Ellis ((443) 995-1745)',
        ], $this->venue->contact());
    }

    public function testGetStreet()
    {
        $this->assertEquals('726 Donaldson Avenue', $this->venue->getStreet());
    }

    public function testGetCity()
    {
        $this->assertEquals('Severn', $this->venue->getCity());
    }

    public function testGetStateAbbr()
    {
        $this->assertEquals('MD', $this->venue->getStateAbbr());
    }

    public function testgetZipCode()
    {
        $this->assertEquals('21144', $this->venue->getZipCode());
    }

    public function testGetCountry()
    {
        $this->assertEquals('USA', $this->venue->getCountry());
    }

    public function testParseLinksUri()
    {
        $schedule = $this->venue->parseScheduleUri();
        $map      = $this->venue->parseMapUri();
        $website  = $this->venue->parseWebsiteUri();

        $this->assertEquals('/tracks/1623/events/schedule', $schedule);
        $this->assertEquals('http://maps.google.com/maps?q=39.138649,-76.681566&iwloc=A&iwd=1', $map);
        $this->assertEquals('http://www.usabmx.com/tracks/1623', $website);
    }

    public function testParseLogo()
    {
        $this->assertEquals(
            '//s3.amazonaws.com/bmxwebserverprod/attachments/187062/track_logo_mxw200_mxha_e0.jpg',
            $this->venue->parseLogo()
        );
    }

    public function testParseDescription()
    {
        $this->assertEquals(
            'Chesapeake BMX, ranked #2 among all tracks on the East Coast. Come out  today and check out the awesome sport of Bicycle Motocross Racing! Directions: Track is located at 726 Donaldson Ave at the Severn-Danza Athletic Park.',
            $this->venue->parseDescription()
        );
    }

    public function testParseDistrict()
    {
        $this->assertEquals('MD01', $this->venue->parseDistrict());
    }

    public function testParseLatLongFromMapsUri()
    {
        $this->assertArraySubset([
            'lat'  => 39.138649,
            'long' => -76.681566,
        ], $this->venue->parseLatLongMapsUri());
    }

    public function testParseName()
    {
        $this->assertEquals('Chesapeake BMX', $this->venue->parseName());
    }
}
