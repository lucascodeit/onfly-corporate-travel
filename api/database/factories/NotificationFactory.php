<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'user_to_id' => User::factory(),
            'user_from_id' => User::factory()->admin(),
            'request_id' => TravelRequest::factory(),
            'notification_type' => 'response_travel',
            'message' => fake()->sentence(),
            'is_read' => false,
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function general(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_type' => 'general',
            'request_id' => null,
        ]);
    }
}
