<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\EventRequest;
use App\Models\User;
use App\Models\Event;

/**
 * @extends Factory<EventRequest>
 */
class EventRequestFactory extends Factory
{
    protected $model = EventRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'event_id' => Event::factory(),
            'status' => 'pending',
            'guests' => rand(1,5),
            'notes' => $this->faker->sentence(),
        ];
    }
}
