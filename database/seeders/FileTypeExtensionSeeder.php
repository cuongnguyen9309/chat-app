<?php

namespace Database\Seeders;

use App\Models\FileType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FileTypeExtensionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $imageFileType = FileType::where('name', 'image')->first();
        $audioFileType = FileType::where('name', 'audio')->first();
        $videoFileType = FileType::where('name', 'video')->first();
        $textFileType = FileType::where('name', 'text')->first();
        $items = [
            ['file_type_id' => $imageFileType->id, 'name' => 'png'],
            ['file_type_id' => $imageFileType->id, 'name' => 'jpg'],
            ['file_type_id' => $imageFileType->id, 'name' => 'jpeg'],
            ['file_type_id' => $audioFileType->id, 'name' => 'mp3'],
            ['file_type_id' => $videoFileType->id, 'name' => 'mp4'],
            ['file_type_id' => $textFileType->id, 'name' => 'txt'],
        ];
        DB::table('file_type_extension')->insert($items);
    }
}
