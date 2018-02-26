<?php

namespace Tests\Unit;

use Tests\TestCase;

use Goutte\Client as GoutteClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

use App\ShovelEvent;

class ShovelEventTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $eventHtml = file_get_contents(base_path('tests/Unit/data/single-event.html'));

        $mock = new MockHandler([
            new Response(200, ['Content-type' => 'text/html'], $eventHtml),
        ]);

        $httpClient = new GuzzleClient([
            'handler' => HandlerStack::create($mock),
        ]);

        $goutte = new GoutteClient();
        $goutte->setClient($httpClient);

        $event = new ShovelEvent(123);
        $event->setClient($goutte);
        $this->event = $event;
    }

    public function testIdFromShareLinks()
    {
        $this->assertEquals(334979, $this->event->idFromShareLinks());
    }

    public function testTitle()
    {
        $this->assertEquals('State Race Double', $this->event->title());
    }

    public function testVenueId()
    {
        $this->assertEquals(196, $this->event->venueId());
    }

    public function testUrl()
    {
        $this->assertEquals('http://usabmx.com/site/bmx_races/123', $this->event->url());
    }

    public function testFee()
    {
        $this->assertEquals(25, $this->event->fee());
    }

    public function testRegistrationStartTime()
    {
        $this->assertEquals('9:00 AM', $this->event->registrationStartTime());
    }

    public function testRegistrationEndTime()
    {
        $this->assertEquals('11:00 AM', $this->event->registrationEndTime());
    }

    public function testEventTypeByTitle()
    {
        $this->assertEquals('State', $this->event->getTypeFromTitle());
    }
}
