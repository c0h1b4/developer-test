<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_basic_test()
    {
        // user's data
        $user = [
            'email' => 'test@gmail.com',
            'name' => 'test',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ];

        // send request to register
        $response = $this->json('POST', route('api.register'), $user);
        // assert it was successful
        $response->assertStatus(200);
        // $this->assertTrue(true);
    }
}
