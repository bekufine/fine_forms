<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(VisitSatisfactionSurveySeeder::class);
        $this->call(SalesOfficeVisitSurveySeeder::class);
        $this->call(GoogleReviewOfficeSurveySeeder::class);
        $this->call(FreshFishFreeSampleSurveySeeder::class);
        $this->call(FreshFishPostUseSurveySeeder::class);
        $this->call(HashimotoHonshaUserSeeder::class);
    }
}
