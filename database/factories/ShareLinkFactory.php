<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ShareLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShareLink>
 */
class ShareLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'token' => ShareLink::generateUniqueToken(),
            'name' => fake()->words(3, true),
            'password' => null,
            'is_active' => true,
            'expires_at' => null,
            'view_count' => 0,
            'last_viewed_at' => null,
        ];
    }

    /**
     * Indicate the share link has a password.
     */
    public function withPassword(string $password = 'password'): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => $password,
        ]);
    }

    /**
     * Indicate the share link is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate the share link has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate the share link expires in the future.
     */
    public function expiresIn(int $days = 7): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->addDays($days),
        ]);
    }
}
