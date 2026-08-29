<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/** @extends Factory<\App\Models\User> */
class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => \Illuminate\Support\Str::random(10),
            'role' => 'student',
        ];
    }

    public function admin(): static { return $this->state(fn () => ['role' => 'admin']); }
    public function teacher(): static { return $this->state(fn () => ['role' => 'teacher']); }
    public function student(): static { return $this->state(fn () => ['role' => 'student']); }
}
