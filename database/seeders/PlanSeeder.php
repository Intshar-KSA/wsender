<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        try{
        if (Plan::count() == 0) {
            Plan::create([
                'title' => 'Free Plan',
                'number_of_days' => 0,
                'price' => 0,
                'is_free' => true,
                'hours' => 20,
            ]);

            Plan::create([
                'title' => 'Monthly Plan',
                'number_of_days' => 30,
                'price' => 100,
            ]);

            Plan::create([
                'title' => 'Three Months Plan',
                'number_of_days' => 90,
                'price' => 270,
            ]);

            Plan::create([
                'title' => 'Six Months Plan',
                'number_of_days' => 180,
                'price' => 500,
            ]);

            Plan::create([
                'title' => 'Yearly Plan',
                'number_of_days' => 365,
                'price' => 900,
            ]);
        }
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}
}
