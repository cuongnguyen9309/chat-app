<?php

namespace Database\Seeders;

use App\Models\GroupMessage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GroupMessage::factory(500)->create();
        $groupMessages = GroupMessage::all();
        foreach ($groupMessages as $groupMessage) {
            $sender_id = $groupMessage->sender->id;
            $user_ids = $groupMessage->receiver->users->pluck('id')->toArray();
            $groupMessage->unseen_users()->attach($user_ids);
            $groupMessage->unseen_users()->detach($sender_id);
        }
    }
}
