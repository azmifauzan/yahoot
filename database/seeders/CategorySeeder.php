<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed the initial quiz categories.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Umum', 'icon' => '🎯'],
            ['name' => 'Matematika', 'icon' => '🔢'],
            ['name' => 'Sains', 'icon' => '🔬'],
            ['name' => 'Bahasa', 'icon' => '🔤'],
            ['name' => 'Sejarah', 'icon' => '📜'],
            ['name' => 'Geografi', 'icon' => '🌍'],
            ['name' => 'Teknologi', 'icon' => '💻'],
            ['name' => 'Olahraga', 'icon' => '⚽'],
            ['name' => 'Hiburan', 'icon' => '🎬'],
            ['name' => 'Lainnya', 'icon' => '✨'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                ['name' => $category['name'], 'icon' => $category['icon']]
            );
        }
    }
}
