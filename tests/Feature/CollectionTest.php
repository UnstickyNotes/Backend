<?php

use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('lists user collections', function () {
    Collection::factory()->count(2)->create(['user_id' => $this->user->id]);

    Sanctum::actingAs($this->user, ['*']);

    $response = $this->getJson('/api/collections');

    $response->assertStatus(200)
        ->assertJsonPath('status', true)
        ->assertJsonCount(2, 'data');
});

it('creates a collection with a unique name', function () {
    Sanctum::actingAs($this->user, ['*']);

    $response = $this->postJson('/api/collections', [
        'name' => 'My Collection',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', true)
        ->assertJsonPath('data.name', 'My Collection');

    $this->assertDatabaseHas('collections', [
        'name' => 'My Collection',
        'user_id' => $this->user->id,
    ]);
});

it('creates a collection with an auto-incremented name if exists', function () {
    Collection::factory()->create(['name' => 'My Collection', 'user_id' => $this->user->id]);

    Sanctum::actingAs($this->user, ['*']);

    $response = $this->postJson('/api/collections', [
        'name' => 'My Collection',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', true)
        ->assertJsonPath('data.name', 'My Collection-1');
});

it('fails to create collection without a name', function () {
    Sanctum::actingAs($this->user, ['*']);

    $response = $this->postJson('/api/collections', []);

    $response->assertStatus(422);
});

it('updates a collection', function () {
    $collection = Collection::factory()->create(['user_id' => $this->user->id, 'name' => 'Old Name']);

    Sanctum::actingAs($this->user, ['*']);

    $response = $this->putJson("/api/collections/{$collection->id}", [
        'name' => 'New Name',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('status', true);

    $this->assertDatabaseHas('collections', [
        'id' => $collection->id,
        'name' => 'New Name',
    ]);
});

it('deletes a collection', function () {
    $collection = Collection::factory()->create(['user_id' => $this->user->id]);

    Sanctum::actingAs($this->user, ['*']);

    $response = $this->deleteJson("/api/collections/{$collection->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('collections', [
        'id' => $collection->id,
    ]);
});
