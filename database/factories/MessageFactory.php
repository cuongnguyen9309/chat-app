<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sender_id = User::all()->random();
        $receiver_id = $sender_id->friends->random();
        return [
            'content' => fake()->paragraph(rand(3, 7)),
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id
        ];
    }
}
