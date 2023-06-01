<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FileTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'image', 'image_url' => null],
            ['name' => 'audio', 'image_url' => '/images/file_thumbnails/audio.png'],
            ['name' => 'video', 'image_url' => '/images/file_thumbnails/video.png'],
            ['name' => 'compressed', 'image_url' => '/images/file_thumbnails/compressed.png'],
            ['name' => 'database', 'image_url' => '/images/file_thumbnails/database.png'],
            ['name' => 'disc', 'image_url' => '/images/file_thumbnails/disc.png'],
            ['name' => 'email', 'image_url' => '/images/file_thumbnails/email.png'],
            ['name' => 'executable', 'image_url' => '/images/file_thumbnails/executable.png'],
            ['name' => 'font', 'image_url' => '/images/file_thumbnails/font.png'],
        ];
        DB::table('file_types')
            ->insert($items);
    }
}
