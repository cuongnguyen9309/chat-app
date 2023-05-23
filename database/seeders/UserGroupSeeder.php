<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Group::all() as $group) {
            $users = User::all()->random(rand(1, 3))->pluck('id')->toArray();
            $group->users()->attach($users, ['status' => 'accepted']);
            $group->update(['admin_id' => $users[0]]);
        }
    }
}
