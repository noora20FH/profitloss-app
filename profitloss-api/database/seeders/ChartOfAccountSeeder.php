<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChartOfAccount;
use App\Models\CoaCategory;
use Illuminate\Support\Facades\DB;

class ChartOfAccountSeeder extends Seeder
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
        ChartOfAccount::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ambil ID kategori yang sudah ada
        $categoryMap = CoaCategory::pluck('id', 'name')->toArray();

        $accounts = [
            // Income
            ['code' => '401', 'name' => 'Gaji Karyawan', 'category_name' => 'Salary'],
            ['code' => '402', 'name' => 'Gaji Ketua MPR', 'category_name' => 'Salary'],
            ['code' => '403', 'name' => 'Profit Trading', 'category_name' => 'Other Income'],

            // Expense
            ['code' => '601', 'name' => 'Biaya Sekolah', 'category_name' => 'Family Expense'],
            ['code' => '602', 'name' => 'Bensin', 'category_name' => 'Transport Expense'],
            ['code' => '603', 'name' => 'Parkir', 'category_name' => 'Transport Expense'],
            ['code' => '604', 'name' => 'Makan Siang', 'category_name' => 'Meal Expense'],
            ['code' => '605', 'name' => 'Makanan Pokok Bulanan', 'category_name' => 'Meal Expense'],
        ];

        foreach ($accounts as $account) {
            // Pastikan kategori ditemukan sebelum membuat COA
            $categoryId = $categoryMap[$account['category_name']] ?? null;
            
            if ($categoryId) {
                ChartOfAccount::create([
                    'code' => $account['code'],
                    'name' => $account['name'],
                    'category_id' => $categoryId,
                ]);
            } else {
                $this->command->error('Kategori ' . $account['category_name'] . ' tidak ditemukan. Lewati COA ' . $account['code']);
            }
        }

        $this->command->info('Chart of Accounts seeded successfully.');
    }
}