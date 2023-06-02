<?php

namespace Database\Seeders;

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
        $items = [
            ['file_type_id' => 1, 'name' => 'png'],
            ['file_type_id' => 10, 'name' => 'txt'],
        ];
        DB::table('file_type_extension')->insert($items);
    }
}
