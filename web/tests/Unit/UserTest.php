<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Laravel\Passport\Passport;

class UserTest extends TestCase
{

    use DatabaseMigrations;
    use DatabaseTransactions;

    public function testUserCanAccessApi()
    {
        Passport::actingAs(
            factory(\App\User::class)->create()
        );

        $response = $this->get('/api/user');
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertStatus(200);
    }
}
