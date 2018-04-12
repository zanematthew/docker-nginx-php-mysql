<?php

namespace Tests\Unit;

use Tests\TestCase;
use Goutte\Client as GoutteClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use App\ShovelEventsSchedule;

class ShovelEventsScheduleTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        // @TODO Move this into its own custom parent setUp(); ?
        $venueHtml = file_get_contents(base_path('tests/Unit/data/venue-schedule-1623.html'));
        $mock      = new MockHandler([
            new Response(404, ['Content-type' => 'text/html'], $venueHtml),
        ]);

        $httpClient = new GuzzleClient([
            'handler' => HandlerStack::create($mock),
        ]);

        $goutte = new GoutteClient();
        $goutte->setClient($httpClient);

        $venueSchedule = new ShovelEventsSchedule(1623, 359, 2017, 5);
        $venueSchedule->setClient($goutte);
        $this->venueSchedule = $venueSchedule;
    }

    public function testBuildUlr(){
        $this->assertEquals(
            'http://usabmx.com/tracks/1623/events/schedule?mode=calendar&month=5&year=2017',
            $this->venueSchedule->buildUrl()
        );
    }

    public function testUsaBmxEventIds()
    {
        $this->assertTrue(in_array(347793, $this->venueSchedule->usaBmxEventIds()));
    }

    public function testFeeFix()
    {
        $this->assertEquals(10, $this->venueSchedule->feeFix('$$10'));
        $this->assertEquals(10, $this->venueSchedule->feeFix('$10'));
        $this->assertEquals(10, $this->venueSchedule->feeFix('$10.00'));
        $this->assertEquals('10.50', $this->venueSchedule->feeFix('$10.50 USD'));
    }

    public function testWtfTimeFix()
    {
        $this->assertEquals('7PM', $this->venueSchedule->wtfTimeFix('7M'));
        $this->assertEquals('7PM', $this->venueSchedule->wtfTimeFix('7', 'practice'));
        $this->assertEquals('7', $this->venueSchedule->wtfTimeFix('7'));
        $this->assertEquals('7PM', $this->venueSchedule->wtfTimeFix('7PM', 'clinic'));
    }

    public function testEvents()
    {
        $this->assertEquals(11, $this->venueSchedule->count());
        $this->assertArraySubset([
            'registration_start_time' => '17:45',
            'registration_end_time'   => '20:30',
            'type'                    => 'Clinic',
            'fee'                     => '',
            'usabmx_id'               => '347793',
            'start_date'              => '2017-05-01',
            'title'                   => 'Clinic',
            'usabmx_venue_id'         => 359,
            'description'             => 'NEW RIDER SKILLS CLINIC STARTING AT 6:00 PM RUNS ABOUT 90 MINUTES...$20 FEE ADVANCE SKILLS CLINIC 7:30-8:30...$10 FEE',
        ], $this->venueSchedule->events()[0][0]);
    }
}
