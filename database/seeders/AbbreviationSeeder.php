<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbbreviationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['short' => 'asap', 'full' => 'as soon as possible'],
            ['short' => 'yolo', 'full' => 'you only live once'],
            ['short' => 'rsvp', 'full' => 'please reply'],
            ['short' => 'lmk', 'full' => 'let me know'],
            ['short' => 'brb', 'full' => 'be right back'],
            ['short' => 'imo', 'full' => 'in my opinion'],
            ['short' => 'diy', 'full' => 'do it yourself'],
        ];
        DB::table('abbreviations')->insert($items);
    }
}
