<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class NoteTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Create a new user.
     *
     * @return string
     */
    public function testRegister()
    {
        $response = $this->postJson ('api/register', ['name' => 'Tom']);
        $response
            ->assertStatus(401)
            ->assertJson([
                'password' => ['The password field is required.'],
            ]);

        $user = factory(User::class)->make();

        $response = $this->postJson ('api/register', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]);

        $this->assertTrue(isset($response['access_token']));

        return $response['access_token'];
    }
}
