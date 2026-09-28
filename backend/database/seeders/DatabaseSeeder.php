<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CatalogSeeder::class);
        if (config('community.demo') && app()->environment('local', 'testing')) {
            $this->call(DemoSeeder::class);
        }
    }
}
