<?php

namespace Thtg88\Journalism\Tests\TestClasses\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Thtg88\Journalism\Tests\TestClasses\Models\User;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'email'             => $this->faker->unique()->safeEmail,
            'email_verified_at' => now()->toDateTimeString(),
            'password'          => 'password',
            'remember_token'    => Str::random(10),
        ];
    }
}
