<?php

namespace App\Containers\AppSection\User\Data\Factories;

use App\Containers\AppSection\User\Models\UserUUID;
use App\Ship\Parents\Factories\Factory as ParentFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserUUIDFactory extends ParentFactory
{
    protected $model = UserUUID::class;

    public function definition(): array
    {
        static $password;

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $password ?: $password = Hash::make('testing-password'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'gender' => $this->faker->randomElement(['male', 'female', 'unspecified']),
            'birth' => $this->faker->date(),
        ];
    }

    public function admin(): static
    {
        return $this->afterCreating(function (UserUUID $user) {
            $user->assignRole(config('appSection-authorization.admin_role'));
        });
    }

    public function unverified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
