<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Folder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
//        Folder::create([
//            'name'=>'root',
//            'parent_id'=>null,
//        ]);

        $this->call(FolderSeeder::class);
    }
}
