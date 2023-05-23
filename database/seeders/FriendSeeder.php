<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FriendSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (User::all() as $user) {
            $friends = User::all()->random(rand(0, 4));
            $friends = $friends->reject(function ($item) use ($user) {
                return $item->id === $user->id;
            });
            $user->friends()->syncWithoutDetaching($friends->pluck('id')->toArray());
            foreach ($friends as $friend) {
                $friend->friends()->syncWithoutDetaching($user->id);
            }
        }
    }
}
