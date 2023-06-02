<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'smile', 'image_url' => '/images/reactions/smile.png'],
            ['name' => 'thumbs_up', 'image_url' => '/images/reactions/thumbs_up.png'],
            ['name' => 'crying', 'image_url' => '/images/reactions/crying.png'],
            ['name' => 'heart', 'image_url' => '/images/reactions/heart.png'],
        ];
        DB::table('reactions')->insert($items);
    }
}
