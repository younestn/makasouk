<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => User::ROLE_CUSTOMER,
            'is_suspended' => false,
            'approved_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): self
    {
        return $this->state(fn () => ['role' => User::ROLE_ADMIN]);
    }

    public function tailor(): self
    {
        return $this->state(fn () => ['role' => User::ROLE_TAILOR]);
    }
}
