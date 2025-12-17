<?php

namespace Database\Factories;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $start = Carbon::now()->addDay()->setMinute(0);
        $end   = (clone $start)->addMinutes(60);

        return [
            'user_id'    => User::factory(),
            'space_id'   => Space::factory(),
            'event_name' => $this->faker->sentence(3),
            'start_time' => $start,
            'end_time'   => $end,
            'active'     => true,
        ];
    }
}
