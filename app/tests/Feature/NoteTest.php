<?php

namespace Tests\Feature;

use App\User;
use App\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
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
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
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
     * @return void
     */
    public function testDeletedNote(array $params)
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
     * @return void
     */
    public function testRestoreNote(array $params)
    {
        $response = $this->get(route('note.restore', $params['id']) . '?token=' . $params['token']);
        $response
            ->assertOk()
            ->assertJson([
                'id' => $params['id']
            ]);
    }

    /**
     * Added attache for note.
     *
     * @param array $params token and noteId
     *
     * @depends testAddNote
     *
     * @return void
     */
    public function testAddAttacheForNote(array $params)
    {
        // added atache
        copy (__DIR__ . '/_files/_test.jpg', __DIR__ . '/_files/test.jpg');
        $file = new UploadedFile (__DIR__ . '/_files/test.jpg', 'test.jpg', 'image/jpeg', null, true, true);

        $response = $this->postJson(route('note.addfile', $params['id']) . '?token=' . $params['token'], [
            'attache' => $file,
        ]);

        $response->assertOk()
            ->assertJson([
                'file' => $params['id'] . '.jpg',
            ]);


    }

    /**
     * Get notes for user
     *
     * @param array $params token and noteId
     *
     * @depends testAddNote
     *
     * @return void
     */
    public function testGetNotes(array $params)
    {
        $response = $this->get(route('note.index') . '?token=' . $params['token']);
        $response->assertOk();
    }
}
