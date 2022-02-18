<?php

use App\Helpers\JWT;
use App\Models\User;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->get('/');

        $this->assertEquals(
            $this->app->version(),
            $this->response->getContent()
        );

        $user = [
            'email' =>  uniqid() . '@test.com',
            'password' => '123456'
        ];
        // test register
        $this->json('POST', '/api/register', $user)
             ->seeStatusCode(200);

        // test login
        $login = $this->json('POST', '/api/login', $user)
             ->seeStatusCode(200);
    }
}
