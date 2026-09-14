<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;

class HomeArticleCategorySeeder extends Seeder
{
    public function run(): void
    {
        ArticleCategory::firstOrCreate(
            ['slug' => 'csr-environment'],
            [
                'name' => ['en' => 'CSR & Environment', 'id' => 'CSR & Lingkungan'],
                'color' => '#30aa47',
                'sort_order' => 1,
            ]
        );

        ArticleCategory::firstOrCreate(
            ['slug' => 'insights-trends'],
            [
                'name' => ['en' => 'Insights & Trends', 'id' => 'Wawasan & Tren'],
                'color' => '#268839',
                'sort_order' => 2,
            ]
        );
    }
}
