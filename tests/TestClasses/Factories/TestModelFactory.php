<?php

namespace Thtg88\Journalism\Tests\TestClasses\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Thtg88\Journalism\Tests\TestClasses\Models\TestModel;

class TestModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TestModel::class;

    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'end_date'   => $this->faker->dateTime(),
            'start_date' => function (array $data) {
                return $this->faker->dateTime($data['end_date']);
            },
            'uuid' => (string) Str::uuid(),
        ];
    }
}
