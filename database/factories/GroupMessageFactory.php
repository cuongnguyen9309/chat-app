<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupMessage>
 */
class GroupMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $receiver_id = Group::all()->random();
        $sender_id = $receiver_id->users->random();
        return [
            'content' => fake()->paragraph(rand(3, 7)),
            'receiver_id' => $receiver_id,
            'sender_id' => $sender_id
        ];
    }
}
