<?php

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = [
            [
                'title' => 'Jazz Night at After Eight',
                'description' => 'Join us for an evening of smooth jazz with live performances by local artists. Enjoy cocktails and appetizers while listening to the best jazz music in town.',
                'location' => 'After Eight Main Stage',
                'event_date' => Carbon::now()->addDays(10)->toDateString(),
                'event_time' => '20:00:00',
                'capacity' => 150,
                'price' => 45.00,
                'status' => EventStatus::PUBLISHED,
                'terms_conditions' => 'No outside food or drinks allowed. Must be 18+ to attend.',
            ],
            [
                'title' => 'Wine Tasting Evening',
                'description' => 'Discover exquisite wines from around the world paired with gourmet cheese and crackers. Our sommelier will guide you through each tasting.',
                'location' => 'After Eight Wine Cellar',
                'event_date' => Carbon::now()->addDays(15)->toDateString(),
                'event_time' => '19:30:00',
                'capacity' => 50,
                'price' => 65.00,
                'status' => EventStatus::PUBLISHED,
                'terms_conditions' => 'Must be 21+ to attend. Valid ID required.',
            ],
            [
                'title' => 'Live Comedy Show',
                'description' => 'Laugh the night away with our featured comedians. A night full of humor and entertainment awaits you.',
                'location' => 'After Eight Comedy Club',
                'event_date' => Carbon::now()->addDays(7)->toDateString(),
                'event_time' => '21:00:00',
                'capacity' => 100,
                'price' => 35.00,
                'status' => EventStatus::PUBLISHED,
                'terms_conditions' => 'Adult content. Must be 18+ to attend.',
            ],
            [
                'title' => 'Acoustic Session',
                'description' => 'An intimate acoustic performance featuring singer-songwriters. Perfect for a relaxed evening with friends.',
                'location' => 'After Eight Lounge',
                'event_date' => Carbon::now()->addDays(5)->toDateString(),
                'event_time' => '19:00:00',
                'capacity' => 75,
                'price' => 25.00,
                'status' => EventStatus::PUBLISHED,
                'terms_conditions' => 'All ages welcome.',
            ],
            [
                'title' => 'New Year\'s Eve Gala',
                'description' => 'Ring in the new year with style! A black-tie event featuring dinner, dancing, and a champagne toast at midnight.',
                'location' => 'After Eight Grand Ballroom',
                'event_date' => Carbon::now()->addMonths(3)->lastOfMonth()->toDateString(),
                'event_time' => '20:00:00',
                'capacity' => 200,
                'price' => 150.00,
                'status' => EventStatus::DRAFT,
                'terms_conditions' => 'Black-tie dress code required. Must be 21+ to attend.',
            ],
            [
                'title' => 'Monthly Trivia Night',
                'description' => 'Test your knowledge in our monthly trivia competition. Prizes for the winning team!',
                'location' => 'After Eight Main Floor',
                'event_date' => Carbon::now()->addDays(20)->toDateString(),
                'event_time' => '18:30:00',
                'capacity' => 120,
                'price' => 15.00,
                'status' => EventStatus::PUBLISHED,
                'terms_conditions' => 'Teams of up to 6 people. All ages welcome.',
            ],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
