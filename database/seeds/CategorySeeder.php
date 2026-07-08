<?php

use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Default credentials
        \App\Category::insert([
            [ 
                'name' => 'Category Number 1',
                'status' => 1
            ]
        ]);
    }
}
