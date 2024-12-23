<?php declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cart>
 */
class CartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAndUpdatedAt = $this->faker->dateTimeBetween('-3 months', 'now');
        return [
            'user_id' => null,
            'session_id' => $this->faker->sha1(),
            'active' => $this->faker->boolean(),
            'created_at' => $createdAndUpdatedAt,
            'updated_at' => $createdAndUpdatedAt,
        ];
    }
}
