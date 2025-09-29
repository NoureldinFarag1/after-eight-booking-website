<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Event;
use App\Models\User;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 days', '+10 days');
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'location' => $this->faker->city(),
            'start_time' => $start,
            'end_time' => (clone $start)->modify('+2 hours'),
            'status' => 'draft',
            'capacity' => rand(50,200),
            'created_by' => User::factory(),
        ];
    }
}
