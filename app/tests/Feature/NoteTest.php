<?php

namespace Tests\Feature;

use App\User;
use App\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NoteTest extends TestCase
{
    /**
     * Create user
     *
     * @return string
     */
    public function testRegister()
    {
        // registration
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

    /**
     * Added new note.
     *
     * @param string $token
     *
     * @depends testRegister
     *
     * @return array
     */
    public function testAddNote($token)
    {
        $response = $this->postJson(route('note.store'));
        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);

        $response = $this->postJson(route('note.store') . '?token=' . $token);
        $response
            ->assertStatus(400)
            ->assertJson([
                'note' => ['The note field is required.']
            ]);

        $note = factory(Note::class)->make();
        $response = $this->postJson(route('note.store') . '?token=' . $token, [
            'note' => $note->text,
        ]);

        $response->assertOk()
            ->assertJson([
                'text' => $note->text,
            ]);

        return [
            'token' => $token,
            'id' => $response['id'],
        ];
    }

    /**
     * Delete a note
     *
     * @param $params
     *
     * @depends testAddNote
     *
     * @return null
     */
    public function testDeletedNote($params)
    {
        $response = $this->delete(route('note.destroy', $params['id']) . '?token=' . $params['token']);
        $response
            ->assertOk()
            ->assertJson([
                'deleted' => $params['id']
            ]);
    }

    /**
     * Restore a note
     *
     * @param $params
     *
     * @depends testAddNote
     *
     * @return null
     */
    public function testRestoreNote($params)
    {
        $response = $this->get(route('note.restore', $params['id']) . '?token=' . $params['token']);
        $response
            ->assertOk()
            ->assertJson([
                'id' => $params['id']
            ]);
    }
}
