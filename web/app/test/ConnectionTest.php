<?php

namespace AppTest\Acme;

use App\Acme\Connection;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    public function testConnect()
    {
        $foo = new Connection();
        $this->assertTrue($foo->connect());
    }
}
