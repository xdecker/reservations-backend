<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Space;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SpaceTest extends TestCase
{
    use RefreshDatabase;

    private function adminToken()
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        return auth()->login($admin);
    }

    private function userToken()
    {
        $user = User::factory()->create([
            'role' => 'USER',
        ]);

        return auth()->login($user);
    }


    public function test_anyone_can_list_spaces()
    {
        $user = User::factory()->create([
            'role' => 'USER'
        ]);

        Space::factory()->count(3)->create();

        $token = auth()->login($user);

        $response = $this
            ->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/spaces');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'message'
            ]);
    }


    public function test_anyone_can_view_a_single_space()
    {
        $user = User::factory()->create([
            'role' => 'USER'
        ]);

        $space = Space::factory()->create();

        $token = auth()->login($user);

        $response = $this
            ->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/spaces/{$space->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'capacity'],
                'message'
            ]);
    }


    public function test_admin_can_create_a_space()
    {
        $token = $this->adminToken();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/spaces', [
                'name' => 'Sala Principal',
                'capacity' => 50,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'capacity'],
                'message'
            ]);
    }


    public function test_no_admin_cannot_create_a_space()
    {
        $token = $this->userToken();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/spaces', [
                'name' => 'Sala Hack',
                'capacity' => 20,
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_a_space()
    {
        $token = $this->adminToken();

        $space = Space::factory()->create([
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/spaces/{$space->id}", [
                'name' => 'Sala Editada',
                'capacity' => 99,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Sala Editada');
    }


    public function test_no_admin_cannot_update_a_space()
    {
        $token = $this->userToken();

        $space = Space::factory()->create([
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/spaces/{$space->id}", [
                'name' => 'Hack',
            ]);

        $response->assertStatus(403);
    }


    public function test_admin_can_delete_a_space()
    {
        $token = $this->adminToken();

        $space = Space::factory()->create([
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/spaces/{$space->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('spaces', [
            'id' => $space->id,
            'active' => false,
        ]);
    }

    public function test_no_admin_cannot_delete_a_space()
    {
        $token = $this->userToken();

        $space = Space::factory()->create([
            'active' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/spaces/{$space->id}");

        $response->assertStatus(403);
    }
}
