<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CoaCategory;
use Illuminate\Support\Facades\DB;

class CoaCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Nonaktifkan Foreign Key Check sementara untuk truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        CoaCategory::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $categories = [
            'Salary',
            'Other Income',
            'Family Expense',
            'Transport Expense',
            'Meal Expense',
        ];

        foreach ($categories as $name) {
            CoaCategory::create(['name' => $name]);
        }

        $this->command->info('Coa Categories seeded successfully.');
    }
}