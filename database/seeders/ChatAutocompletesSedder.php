<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatAutocompletesSedder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['content' => 'australia'],
            ['content' => 'america'],
            // Add more items as needed
        ];

        DB::table('chat_autocompletes')->insert($items);
    }
}
