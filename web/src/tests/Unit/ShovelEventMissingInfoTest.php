<?php

namespace Tests\Unit;

use Tests\TestCase;

use Goutte\Client as GoutteClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

use App\ShovelEvent;

class ShovelEventMissingInfoTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $eventHtml = file_get_contents(base_path('tests/Unit/data/single-event-id-1.html'));

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
        $this->assertEquals(1, $this->event->idFromShareLinks());
    }

    public function testTitle()
    {
        $this->assertEquals('Redline Cup Finals East', $this->event->title());
    }

    public function testVenueId()
    {
        $this->assertEmpty($this->event->venueId());
    }

    public function testFee()
    {
        $this->assertEmpty($this->event->fee());
    }

    public function testRegistrationStartTime()
    {
        $this->assertEmpty($this->event->registrationStartTime());
    }

    public function testRegistrationEndTime()
    {
        $this->assertEmpty($this->event->registrationEndTime());
    }
}
