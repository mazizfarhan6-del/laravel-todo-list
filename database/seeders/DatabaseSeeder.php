<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Menambahkan pilihan kategori default
        Category::create(['name' => 'Kerja']);
        Category::create(['name' => 'Kuliah']);
        Category::create(['name' => 'Pribadi']);
    }
}