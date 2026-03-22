<?php

namespace Database\Factories;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TravelRequest>
 */
class TravelRequestFactory extends Factory
{
    protected $model = TravelRequest::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 day', '+30 days');
        $endDate = fake()->dateTimeBetween($startDate, '+60 days');

        return [
            'status' => 'requested',
            'destination' => fake()->city(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_id' => User::factory(),
            'admin_id' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'admin_id' => User::factory()->admin(),
        ]);
    }

    public function disapproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disapproved',
            'admin_id' => User::factory()->admin(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
