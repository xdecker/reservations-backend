<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Space;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    private function authToken(User $user)
    {
        return auth()->login($user);
    }

    private function validPayload(Space $space)
    {
        $start = Carbon::now()
            ->addDay()
            ->setHour(9)
            ->setMinute(0)
            ->setSecond(0);

        $end = (clone $start)->addMinutes(60);

        return [
            'space_id'   => $space->id,
            'event_name' => 'Evento Test',
            'start_time' => $start->toDateTimeString(),
            'end_time'   => $end->toDateTimeString(),
        ];
    }


 
    public function test_authenticated_user_can_list_their_reservations()
    {
        $user = User::factory()->create();
        $token = $this->authToken($user);

        Reservation::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/reservations');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'message']);
    }

    public function test_authenticated_user_can_view_own_reservation()
    {
        $user = User::factory()->create();
        $token = $this->authToken($user);

        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/reservations/{$reservation->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $reservation->id);
    }

    public function test_authenticated_user_can_create_reservation()
    {
        $user = User::factory()->create();
        $token = $this->authToken($user);

        $space = Space::factory()->create([
            'available_from' => '08:00',
            'available_to'   => '18:00',
            'active'         => true,
        ]);
        

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/reservations', $this->validPayload($space));

        $response->assertStatus(201)
            ->assertJsonStructure(['data', 'message']);
    }


    public function test_user_cannot_create_overlapping_reservation()
    {
        $user = User::factory()->create();
        $token = $this->authToken($user);

        $space = Space::factory()->create([
            'available_from' => '08:00',
            'available_to'   => '18:00',
            'active'         => true,
        ]);

        $payload = $this->validPayload($space);

        Reservation::factory()->create([
            'space_id'   => $space->id,
            'start_time' => $payload['start_time'],
            'end_time'   => $payload['end_time'],
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/reservations', $payload);

        $response->assertStatus(422);
    }

    public function test_user_can_update_own_reservation()
    {
        $user = User::factory()->create();
        $space = Space::factory()->create([
            'available_from' => '08:00',
            'available_to'   => '18:00',
            'active' => true,
        ]);
        $token = $this->authToken($user);

        $start = Carbon::now()
            ->addDay()
            ->setHour(9)
            ->setMinute(0)
            ->setSecond(0);

        $reservation = Reservation::factory()->create([
            'user_id'    => $user->id,
            'space_id'   => $space->id,
            'start_time' => $start,
            'end_time'   => (clone $start)->addMinutes(60),
        ]);


        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/reservations/{$reservation->id}", [
                'event_name' => 'Evento Editado',
                'space_id' => $space->id,
                'start_time' => $reservation->start_time->toDateTimeString(),
                'end_time'   => $reservation->end_time->toDateTimeString(),
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.event_name', 'Evento Editado');
    }


    public function test_user_cannot_update_other_users_reservation()
    {
        $token = $this->authToken(User::factory()->create());

        $space = Space::factory()->create([
            'available_from' => '08:00',
            'available_to'   => '18:00',
            'active' => true,
        ]);
        
        $start = Carbon::now()
            ->addDay()
            ->setHour(9)
            ->setMinute(0)
            ->setSecond(0);
        
        $reservation = Reservation::factory()->create([
            'space_id'   => $space->id,
            'start_time' => $start,
            'end_time'   => (clone $start)->addMinutes(60),
        ]);
        

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/reservations/{$reservation->id}", [
                'event_name' => 'Hack',
                'space_id' => $reservation->space_id,
                'start_time' => $reservation->start_time->toDateTimeString(),
                'end_time'   => $reservation->end_time->toDateTimeString(),
            ]);

        $response->assertStatus(404);
    }


    public function test_user_can_delete_own_reservation()
    {
        $user = User::factory()->create();
        $token = $this->authToken($user);

        $reservation = Reservation::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/reservations/{$reservation->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'active' => false,
        ]);
    }
}
