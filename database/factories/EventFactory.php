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
    $dt = $this->faker->dateTimeBetween('+1 days', '+30 days');
    return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'location' => $this->faker->city(),
            'artists' => collect([$this->faker->name(), $this->faker->name()])->join(', '),
            'event_date' => $dt->format('Y-m-d'),
            'event_time' => $dt->format('H:i:s'),
            'capacity' => rand(50,200),
            'status' => 'draft',
        ];
    }
}
