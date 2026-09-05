<?php

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('lists user notes', function () {
    Note::factory()->count(3)->create(['user_id' => $this->user->id]);

    Sanctum::actingAs($this->user, ['*']);

    $response = $this->getJson('/api/notes');

    $response->assertStatus(200)
        ->assertJsonPath('status', true)
        ->assertJsonCount(3, 'data');
});

it('creates a note', function () {
    Sanctum::actingAs($this->user, ['*']);

    $response = $this->postJson('/api/notes', [
        'title' => 'Test Note',
        'body' => 'This is a test note',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', true)
        ->assertJsonPath('data.title', 'Test Note');

    $this->assertDatabaseHas('notes', [
        'title' => 'Test Note',
        'user_id' => $this->user->id,
    ]);
});

it('fails to create note without title or body', function () {
    Sanctum::actingAs($this->user, ['*']);

    $response = $this->postJson('/api/notes', []);

    $response->assertStatus(422)
        ->assertJsonPath('status', false);
});

it('shows a specific note belonging to user', function () {
    $note = Note::factory()->create(['user_id' => $this->user->id]);

    Sanctum::actingAs($this->user, ['*']);

    $response = $this->getJson("/api/notes/{$note->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $note->id);
});

it('cannot show note of another user', function () {
    $otherUser = User::factory()->create();
    $note = Note::factory()->create(['user_id' => $otherUser->id]);

    Sanctum::actingAs($this->user, ['*']);

    $response = $this->getJson("/api/notes/{$note->id}");

    $response->assertStatus(403);
});

it('updates a note', function () {
    $note = Note::factory()->create(['user_id' => $this->user->id]);

    Sanctum::actingAs($this->user, ['*']);

    $response = $this->putJson("/api/notes/{$note->id}", [
        'title' => 'Updated Title',
        'body' => 'Updated Body',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.title', 'Updated Title');

    $this->assertDatabaseHas('notes', [
        'id' => $note->id,
        'title' => 'Updated Title',
        'body' => 'Updated Body',
    ]);
});

it('deletes a note', function () {
    $note = Note::factory()->create(['user_id' => $this->user->id]);

    Sanctum::actingAs($this->user, ['*']);

    $response = $this->deleteJson("/api/notes/{$note->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('notes', [
        'id' => $note->id,
    ]);
});
