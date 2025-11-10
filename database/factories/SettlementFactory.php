<?php

namespace Database\Factories;

use App\Models\Settlement;
use App\Models\County;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettlementFactory extends Factory
{
    protected $model = Settlement::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->city(),
            'county_id' => County::factory(),
        ];
    }
}