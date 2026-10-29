<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultCategories = ['Sekolah', 'PKL', 'Pribadi'];

        foreach ($defaultCategories as $categoryName) {
            Category::firstOrCreate(['name' => $categoryName]);
        }
    }
}

