<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(20)->create();
        $admin = User::create([
            'name' => 'Nguyen Van Test',
            'email' => 'test@test.com',
            'password' => Hash::make('greenadmin'),
            'is_admin' => 1]);
        $users = User::all();
        foreach ($users as $user) {
            $short_url = createShortUrl(route('friend.add.no.confirm', $user->id));
            $user->add_friend_link = $short_url;
            $user->save();
        }
    }
}
