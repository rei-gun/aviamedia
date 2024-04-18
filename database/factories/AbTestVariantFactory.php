<?php

namespace Database\Factories;

use App\Models\AbTestVariant;
use Illuminate\Database\Eloquent\Factories\Factory;


class AbTestVariantFactory extends Factory
{
    protected $model = AbTestVariant::class;
    

    public function definition(): array
    {
        return [
            'ab_test_id' => \App\Models\AbTest::factory(),
            'name' => fake()->word,
            'targeting_ratio' => fake()->numberBetween(1, 10),
        ];
    }
}
