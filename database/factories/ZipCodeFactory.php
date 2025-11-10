<?php

namespace Database\Factories;

use App\Models\ZipCode;
use App\Models\Settlement;
use App\Models\County;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZipCodeFactory extends Factory
{
    protected $model = ZipCode::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numerify('####'),
            'settlement_id' => Settlement::factory(),
            'county_id' => County::factory(),
        ];
    }
}