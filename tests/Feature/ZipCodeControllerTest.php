<?php

namespace Tests\Feature;

use App\Models\ZipCode;
use App\Models\Settlement;
use App\Models\County;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZipCodeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_all_zip_codes(): void
    {
        $county = County::factory()->create(['name' => 'Pest']);
        $settlement = Settlement::factory()->create(['name' => 'Budapest', 'county_id' => $county->id]);
        
        ZipCode::factory()->create(['code' => '1011', 'settlement_id' => $settlement->id, 'county_id' => $county->id]);
        ZipCode::factory()->create(['code' => '1012', 'settlement_id' => $settlement->id, 'county_id' => $county->id]);

        $response = $this->getJson('/api/zip-codes');

        $response->assertStatus(200)
            ->assertJsonFragment(['code' => '1011'])
            ->assertJsonFragment(['code' => '1012']);
    }

    public function test_show_returns_zip_code(): void
    {
        $county = County::factory()->create(['name' => 'Pest']);
        $settlement = Settlement::factory()->create(['name' => 'Budapest', 'county_id' => $county->id]);
        $zipCode = ZipCode::factory()->create([
            'code' => '1011',
            'settlement_id' => $settlement->id,
            'county_id' => $county->id
        ]);

        $response = $this->getJson("/api/zip-codes/{$zipCode->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['code' => '1011']);
    }

    public function test_show_returns_404_for_missing_zip_code(): void
    {
        $response = $this->getJson('/api/zip-codes/999');

        $response->assertStatus(404);
    }

    public function test_store_creates_new_zip_code(): void
    {
        $county = County::factory()->create(['name' => 'Pest']);
        $settlement = Settlement::factory()->create(['name' => 'Budapest', 'county_id' => $county->id]);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/zip-codes', [
            'code' => '1011',
            'settlement_id' => $settlement->id,
            'county_id' => $county->id
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['code' => '1011']);

        $this->assertDatabaseHas('zip_codes', [
            'code' => '1011',
            'settlement_id' => $settlement->id,
            'county_id' => $county->id
        ]);
    }

    public function test_store_validates_required_code(): void
    {
        $county = County::factory()->create();
        $settlement = Settlement::factory()->create(['county_id' => $county->id]);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/zip-codes', [
            'settlement_id' => $settlement->id,
            'county_id' => $county->id
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_store_validates_required_settlement_id(): void
    {
        $county = County::factory()->create();

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/zip-codes', [
            'code' => '1011',
            'county_id' => $county->id
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['settlement_id']);
    }

    public function test_store_validates_required_county_id(): void
    {
        $county = County::factory()->create();
        $settlement = Settlement::factory()->create(['county_id' => $county->id]);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/zip-codes', [
            'code' => '1011',
            'settlement_id' => $settlement->id
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['county_id']);
    }

    public function test_store_validates_settlement_exists(): void
    {
        $county = County::factory()->create();

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/zip-codes', [
            'code' => '1011',
            'settlement_id' => 999,
            'county_id' => $county->id
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['settlement_id']);
    }

    public function test_store_validates_county_exists(): void
    {
        $county = County::factory()->create();
        $settlement = Settlement::factory()->create(['county_id' => $county->id]);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/zip-codes', [
            'code' => '1011',
            'settlement_id' => $settlement->id,
            'county_id' => 999
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['county_id']);
    }

    public function test_store_requires_authentication(): void
    {
        $county = County::factory()->create();
        $settlement = Settlement::factory()->create(['county_id' => $county->id]);

        $response = $this->postJson('/api/zip-codes', [
            'code' => '1011',
            'settlement_id' => $settlement->id,
            'county_id' => $county->id
        ]);

        $response->assertStatus(401);
    }

    public function test_update_modifies_zip_code(): void
    {
        $county = County::factory()->create(['name' => 'Pest']);
        $settlement1 = Settlement::factory()->create(['name' => 'Budapest', 'county_id' => $county->id]);
        $settlement2 = Settlement::factory()->create(['name' => 'Gödöllő', 'county_id' => $county->id]);
        
        $zipCode = ZipCode::factory()->create([
            'code' => '1011',
            'settlement_id' => $settlement1->id,
            'county_id' => $county->id
        ]);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/zip-codes/{$zipCode->id}", [
            'code' => '2100',
            'settlement_id' => $settlement2->id,
            'county_id' => $county->id
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['code' => '2100']);

        $this->assertDatabaseHas('zip_codes', [
            'id' => $zipCode->id,
            'code' => '2100',
            'settlement_id' => $settlement2->id
        ]);
    }

    public function test_update_returns_404_for_missing_zip_code(): void
    {
        $county = County::factory()->create();
        $settlement = Settlement::factory()->create(['county_id' => $county->id]);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/zip-codes/999', [
            'code' => '1011',
            'settlement_id' => $settlement->id,
            'county_id' => $county->id
        ]);

        $response->assertStatus(404);
    }

    public function test_update_requires_authentication(): void
    {
        $county = County::factory()->create();
        $settlement = Settlement::factory()->create(['county_id' => $county->id]);
        $zipCode = ZipCode::factory()->create([
            'settlement_id' => $settlement->id,
            'county_id' => $county->id
        ]);

        $response = $this->putJson("/api/zip-codes/{$zipCode->id}", [
            'code' => '1012',
            'settlement_id' => $settlement->id,
            'county_id' => $county->id
        ]);

        $response->assertStatus(401);
    }

    public function test_destroy_removes_zip_code(): void
    {
        $county = County::factory()->create();
        $settlement = Settlement::factory()->create(['county_id' => $county->id]);
        $zipCode = ZipCode::factory()->create([
            'code' => '1011',
            'settlement_id' => $settlement->id,
            'county_id' => $county->id
        ]);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/zip-codes/{$zipCode->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('zip_codes', ['id' => $zipCode->id]);
    }

    public function test_destroy_returns_404_for_missing_zip_code(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/zip-codes/999');

        $response->assertStatus(404);
    }

    public function test_destroy_requires_authentication(): void
    {
        $county = County::factory()->create();
        $settlement = Settlement::factory()->create(['county_id' => $county->id]);
        $zipCode = ZipCode::factory()->create([
            'settlement_id' => $settlement->id,
            'county_id' => $county->id
        ]);

        $response = $this->deleteJson("/api/zip-codes/{$zipCode->id}");

        $response->assertStatus(401);
    }
}