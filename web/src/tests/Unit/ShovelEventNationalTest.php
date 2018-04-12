<?php

namespace Tests\Unit;

use Tests\TestCase;

use Goutte\Client as GoutteClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

use App\ShovelEvent;

class ShovelEventNationalTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $eventHtml = file_get_contents(base_path('tests/Unit/data/single-event-national.html'));

        $mock = new MockHandler([
            new Response(200, ['Content-type' => 'text/html'], $eventHtml),
        ]);

        $httpClient = new GuzzleClient([
            'handler' => HandlerStack::create($mock),
        ]);

        $goutte = new GoutteClient();
        $goutte->setClient($httpClient);

        $event = new ShovelEvent('https://www.usabmx.com/site/bmx_races');
        $event->setClient($goutte);
        $this->event = $event;
    }

    public function testStartDate()
    {
        $this->assertEquals('June 09, 2017', $this->event->startDate());
    }

    public function testEndDate()
    {
        $this->assertEquals('June 11, 2017', $this->event->endDate());
    }

    public function testFlyerUri()
    {
        $this->assertEquals(
            '//s3.amazonaws.com/bmxwebserverprod/attachments/210852/2017-EAST_COAST.pdf',
            $this->event->flyerUri()
        );
    }

    public function testEventScheduleUri()
    {
        $this->assertEquals(
            '//s3.amazonaws.com/bmxwebserverprod/attachments/204680/2017-3-DAY-SCHEDULE_A.pdf',
            $this->event->eventScheduleUri()
        );
    }

    public function testHotelUri()
    {
        $this->assertEquals('/site/sections/160', $this->event->hotelUri());
    }

    public function testEventTypeByTitle()
    {
        $this->assertEquals('National', $this->event->getTypeFromTitle());
    }
}
