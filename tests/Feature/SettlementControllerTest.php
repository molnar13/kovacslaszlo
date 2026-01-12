<?php

namespace Tests\Feature;

use App\Models\Settlement;
use App\Models\County;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettlementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_settlements(): void
    {
        $county = County::factory()->create(['name' => 'Pest']);
        Settlement::factory()->create(['name' => 'Budapest', 'county_id' => $county->id]);
        Settlement::factory()->create(['name' => 'Gödöllő', 'county_id' => $county->id]);

        $response = $this->getJson('/api/settlements');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Budapest'])
            ->assertJsonFragment(['name' => 'Gödöllő']);
    }

    public function test_show_returns_settlement(): void
    {
        $county = County::factory()->create(['name' => 'Pest']);
        $settlement = Settlement::factory()->create(['name' => 'Budapest', 'county_id' => $county->id]);

        $response = $this->getJson("/api/settlements/{$settlement->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Budapest']);
    }

    public function test_show_returns_404_for_missing_settlement(): void
    {
        $response = $this->getJson('/api/settlements/999');

        $response->assertStatus(404);
    }

    public function test_store_creates_new_settlement()
    {
        $user = \App\Models\User::factory()->create();

        $county = \App\Models\County::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/settlements', [
            'name' => 'Szeged',
            'county_id' => $county->id,
            'postal_code' => '6720'
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Szeged']);
                 
        $this->assertDatabaseHas('settlements', [
            'name' => 'Szeged', 
            'county_id' => $county->id,
            'postal_code' => '6720'
        ]); 
    }

    public function test_store_validates_required_name(): void
    {
        $county = County::factory()->create();

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/settlements', [
            'county_id' => $county->id
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_required_county_id(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/settlements', [
            'name' => 'Budapest'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['county_id']);
    }

    public function test_store_validates_county_exists(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/settlements', [
            'name' => 'Budapest',
            'county_id' => 999
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['county_id']);
    }

    public function test_store_requires_authentication(): void
    {
        $county = County::factory()->create();

        $response = $this->postJson('/api/settlements', [
            'name' => 'Budapest',
            'county_id' => $county->id
        ]);

        $response->assertStatus(401);
    }

    public function test_update_modifies_settlement(): void
    {
        $county1 = County::factory()->create(['name' => 'Pest']);
        $county2 = County::factory()->create(['name' => 'Fejér']);
        $settlement = Settlement::factory()->create(['name' => 'Budapest', 'county_id' => $county1->id]);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/settlements/{$settlement->id}", [
            'name' => 'Székesfehérvár',
            'county_id' => $county2->id
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Székesfehérvár']);

        $this->assertDatabaseHas('settlements', [
            'id' => $settlement->id,
            'name' => 'Székesfehérvár',
            'county_id' => $county2->id
        ]);
    }

    public function test_update_returns_404_for_missing_settlement(): void
    {
        $county = County::factory()->create();

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/settlements/999', [
            'name' => 'Budapest',
            'county_id' => $county->id
        ]);

        $response->assertStatus(404);
    }

    public function test_update_requires_authentication(): void
    {
        $county = County::factory()->create();
        $settlement = Settlement::factory()->create(['county_id' => $county->id]);

        $response = $this->putJson("/api/settlements/{$settlement->id}", [
            'name' => 'Updated Name',
            'county_id' => $county->id
        ]);

        $response->assertStatus(401);
    }

    public function test_destroy_removes_settlement(): void
    {
        $county = County::factory()->create();
        $settlement = Settlement::factory()->create(['name' => 'Budapest', 'county_id' => $county->id]);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/settlements/{$settlement->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('settlements', ['id' => $settlement->id]);
    }

    public function test_destroy_returns_404_for_missing_settlement(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/settlements/999');

        $response->assertStatus(404);
    }

    public function test_destroy_requires_authentication(): void
    {
        $county = County::factory()->create();
        $settlement = Settlement::factory()->create(['county_id' => $county->id]);

        $response = $this->deleteJson("/api/settlements/{$settlement->id}");

        $response->assertStatus(401);
    }
}