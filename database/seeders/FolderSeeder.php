<?php

namespace Database\Seeders;

use App\Models\Folder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Folder::factory()->has(
            Folder::factory(100)
                ->has(
                    Folder::factory(123)
                    ->has(Folder::factory(101),'children')
                ,'children')
        ,'children')->create([
            'name'=>'root',
            'parent_id'=>null,
        ]);


    }
}
