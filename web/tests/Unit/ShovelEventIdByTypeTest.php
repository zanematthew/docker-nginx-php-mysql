<?php

namespace Tests\Unit;

use Tests\TestCase;
use Goutte\Client as GoutteClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use App\ShovelEventIdByType;

class ShovelEventIdByTypeTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        // @TODO Move this into its own custom parent setUp(); ?
        $venueHtml = file_get_contents(base_path('tests/Unit/data/bulk-event.html'));
        $mock      = new MockHandler([
            new Response(404, ['Content-type' => 'text/html'], $venueHtml),
        ]);

        $httpClient = new GuzzleClient([
            'handler' => HandlerStack::create($mock),
        ]);

        $goutte = new GoutteClient();
        $goutte->setClient($httpClient);

        $bulkEventId = new ShovelEventIdByType('National', 2017, 1);
        $bulkEventId->setClient($goutte);
        $this->bulkEventId = $bulkEventId;
    }

    public function testValidUpcomingNationalUrl()
    {
        $this->assertEquals(
            'https://www.usabmx.com/site/bmx_races?section_id=228&year=UPCOMING&past_only=0&page=1&category=NATIONAL',
            $this->bulkEventId->url('National', 2017, 1)
        );
    }

    public function testValidStateUrl()
    {
        $this->assertEquals(
            'https://www.usabmx.com/site/bmx_races?section_id=23&year=2016&past_only=1&page=2&filter_state=1',
            $this->bulkEventId->url('State', 2016, 2)
        );
    }

    public function testValidEarnedDoubleUrl()
    {
        $this->assertEquals(
            'https://www.usabmx.com/site/bmx_races?section_id=95&year=2004&past_only=1&page=5&series_race_type=Earned+Double',
            $this->bulkEventId->url('Earned Double', 2004, 5)
        );
    }

    public function testValidRaceForLifeUrl()
    {
        $this->assertEquals(
            'https://www.usabmx.com/site/bmx_races?section_id=19&year=UPCOMING&past_only=0&page=1&series_race_type=Race+for+Life',
            $this->bulkEventId->url('Race for Life', 2017, 1)
        );
    }

    public function testValidGoldCupUrl()
    {
        $this->assertEquals(
            'https://www.usabmx.com/site/bmx_races?section_id=24&year=UPCOMING&past_only=0&page=1&goldcup=1',
            $this->bulkEventId->url('Gold Cup', 2017, 1)
        );
    }

    public function testPastOnlyNationalUrl()
    {
        $this->assertEquals(
            'https://www.usabmx.com/site/bmx_races?section_id=228&year=2017&past_only=1&page=1&category=NATIONAL',
            $this->bulkEventId->url('National', 2017, 0, 1)
        );
    }

    public function testEventIds()
    {
        $this->assertArraySubset([
            324628,
            325850,
            325851,
            324629,
            324630,
            324631,
            324632,
            325852,
            324633,
            324634,
        ], $this->bulkEventId->eventIds());
    }

    public function testMaxPage()
    {
        $this->assertEquals(3, $this->bulkEventId->maxPage());
    }
}
