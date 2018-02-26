<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\ShovelTrait;

class ShovelTraitTest extends TestCase
{
    use ShovelTrait;

    public function setUp()
    {
        parent::setUp();
        $this->mock = $this->getMockForTrait(ShovelTrait::class);
    }

    public function testIsYearValid()
    {
        $this->assertTrue($this->mock->isYearValid('Upcoming'));
    }

    public function testIsStateValid()
    {
        $this->assertTrue($this->mock->isStateValid('md'));
    }

    public function testIsTypeValid()
    {
        $this->assertTrue($this->mock->isTypeValid('National'));
    }

    public function testIsTypeValidFalse()
    {
        $this->assertFalse($this->mock->isTypeValid('Foo'));
    }

    public function testFalseIsStateValid()
    {
        $this->assertFalse($this->mock->isStateValid('DC'));
    }

    public function testFalseIsYearValid()
    {
        $this->assertFalse($this->mock->isYearValid(2000));
    }

    public function testPageRangeSinglePage()
    {
        $this->assertArraySubset([2,2], $this->mock->parsedPageRange(2));
        $this->assertArraySubset([3,8], $this->mock->parsedPageRange('3-8'));
    }

    public function testPageRange()
    {
        $this->assertArraySubset([5,10], $this->mock->parsedPageRange('5-10'));
    }

    public function testConvertToCents()
    {
        $this->assertEquals(1000, $this->mock->convertToCents('10'));        
        $this->assertEquals(1000, $this->mock->convertToCents('10.00'));
        $this->assertEquals(500, $this->mock->convertToCents('5.00'));
        $this->assertEquals(1050, $this->mock->convertToCents('10.50'));
    }

    public function testTimeFormate()
    {
        $this->assertEquals('19:00', $this->mock->timeFormat('7PM'));
        $this->assertEquals('19:00', $this->mock->timeFormat('7:00 PM'));        
    }
}
