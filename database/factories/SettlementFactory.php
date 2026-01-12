<?php

namespace Database\Factories;

use App\Models\Settlement;
use App\Models\County;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettlementFactory extends Factory
{
    protected $model = Settlement::class;

    public function definition()
    {
        return [
            'name' => $this->faker->city(),
            'county_id' => \App\Models\County::factory(),
            'postal_code' => $this->faker->postcode(), 
        ];
    }
}