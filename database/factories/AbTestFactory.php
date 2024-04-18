<?php

namespace Database\Factories;

use App\Models\AbTest;
use Illuminate\Database\Eloquent\Factories\Factory;

class AbTestFactory extends Factory
{
    protected $model = AbTest::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word,
            'is_active' => true,
        ];
    }
}
