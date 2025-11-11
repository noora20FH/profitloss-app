<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CoaCategory;
use App\Models\ChartOfAccount;
use App\Models\Transaction;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            MasterDataSeeder::class,
        ]);

        // Pastikan tabel dikosongkan terlebih dahulu untuk menghindari duplikasi saat di-run ulang
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Transaction::truncate();
        ChartOfAccount::truncate();
        CoaCategory::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --- 1. COA Categories ---
        $categories = [
            'Salary',
            'Other Income',
            'Family Expense',
            'Transport Expense',
            'Meal Expense',
        ];

        $categoryMap = [];
        foreach ($categories as $name) {
            $category = CoaCategory::create(['name' => $name]);
            $categoryMap[$name] = $category->id; // Simpan ID untuk relasi COA
        }

        $this->command->info('Coa Categories seeded successfully.');

        // --- 2. Chart of Accounts (COA) ---
        $accounts = [
            // Income
            ['code' => '401', 'name' => 'Gaji Karyawan', 'category' => 'Salary'],
            ['code' => '402', 'name' => 'Gaji Ketua MPR', 'category' => 'Salary'],
            ['code' => '403', 'name' => 'Profit Trading', 'category' => 'Other Income'],

            // Expense
            ['code' => '601', 'name' => 'Biaya Sekolah', 'category' => 'Family Expense'],
            ['code' => '602', 'name' => 'Bensin', 'category' => 'Transport Expense'],
            ['code' => '603', 'name' => 'Parkir', 'category' => 'Transport Expense'],
            ['code' => '604', 'name' => 'Makan Siang', 'category' => 'Meal Expense'],
            ['code' => '605', 'name' => 'Makanan Pokok Bulanan', 'category' => 'Meal Expense'],
        ];

        $coaMap = [];
        foreach ($accounts as $account) {
            $coa = ChartOfAccount::create([
                'code' => $account['code'],
                'name' => $account['name'],
                'category_id' => $categoryMap[$account['category']],
            ]);
            $coaMap[$account['code']] = $coa->id; // Simpan ID COA untuk relasi Transaksi
        }

        $this->command->info('Chart of Accounts seeded successfully.');

        // --- 3. Transactions ---
        // Data transaksi ini didasarkan pada contoh Laporan Profit/Loss Anda (Jan, Feb, Mar 2022)
        $transactions = [
            // JANUARI 2022
            // Total Income: 17,500,000 | Total Expense: 850,000
            ['date' => '2022-01-01', 'coa_code' => '401', 'desc' => 'Gaji Di Perusahaan A', 'credit' => 5000000],
            ['date' => '2022-01-02', 'coa_code' => '402', 'desc' => 'Gaji Ketum', 'credit' => 7000000],
            ['date' => '2022-01-05', 'coa_code' => '403', 'desc' => 'Untung Saham Januari', 'credit' => 5500000], // Other Income

            ['date' => '2022-01-10', 'coa_code' => '602', 'desc' => 'Bensin Anak', 'debit' => 25000],
            ['date' => '2022-01-15', 'coa_code' => '601', 'desc' => 'Biaya Sekolah Anak Bulanan', 'debit' => 500000], // Family Expense
            ['date' => '2022-01-18', 'coa_code' => '603', 'desc' => 'Parkir Mall', 'debit' => 25000],
            ['date' => '2022-01-20', 'coa_code' => '604', 'desc' => 'Makan Siang Kantor', 'debit' => 50000],
            ['date' => '2022-01-25', 'coa_code' => '605', 'desc' => 'Belanja Bulanan', 'debit' => 100000], // Total Meal: 150k

            // FEBRUARI 2022
            // Total Income: 18,000,000 | Total Expense: 4,050,000
            ['date' => '2022-02-01', 'coa_code' => '401', 'desc' => 'Gaji Di Perusahaan A', 'credit' => 5000000],
            ['date' => '2022-02-02', 'coa_code' => '402', 'desc' => 'Gaji Ketum', 'credit' => 7000000],
            ['date' => '2022-02-05', 'coa_code' => '403', 'desc' => 'Untung Saham Februari', 'credit' => 6000000], // Other Income

            ['date' => '2022-02-10', 'coa_code' => '601', 'desc' => 'Biaya Sekolah Tambahan', 'debit' => 3500000], // Family Expense
            ['date' => '2022-02-15', 'coa_code' => '602', 'desc' => 'Bensin Pribadi', 'debit' => 200000],
            ['date' => '2022-02-20', 'coa_code' => '603', 'desc' => 'Parkir Bandara', 'debit' => 50000], // Total Transport: 250k
            ['date' => '2022-02-25', 'coa_code' => '604', 'desc' => 'Makan Malam Keluarga', 'debit' => 150000],
            ['date' => '2022-02-28', 'coa_code' => '605', 'desc' => 'Belanja Mingguan', 'debit' => 150000], // Total Meal: 300k

            // MARET 2022
            // Total Income: 15,500,000 | Total Expense: 4,900,000
            ['date' => '2022-03-01', 'coa_code' => '401', 'desc' => 'Gaji Di Perusahaan A', 'credit' => 5000000],
            ['date' => '2022-03-02', 'coa_code' => '402', 'desc' => 'Gaji Ketum', 'credit' => 7000000], // Total Salary: 12M
            ['date' => '2022-03-05', 'coa_code' => '403', 'desc' => 'Untung Saham Maret', 'credit' => 3500000], // Other Income

            ['date' => '2022-03-10', 'coa_code' => '601', 'desc' => 'Biaya Sekolah Tahunan', 'debit' => 4500000], // Family Expense
            ['date' => '2022-03-15', 'coa_code' => '602', 'desc' => 'Bensin Luar Kota', 'debit' => 200000],
            ['date' => '2022-03-20', 'coa_code' => '603', 'desc' => 'Parkir Rumah Sakit', 'debit' => 25000], // Total Transport: 225k
            ['date' => '2022-03-25', 'coa_code' => '604', 'desc' => 'Makan Siang Klien', 'debit' => 100000],
            ['date' => '2022-03-28', 'coa_code' => '605', 'desc' => 'Beli Sembako', 'debit' => 75000], // Total Meal: 175k
        ];

        foreach ($transactions as $t) {
            Transaction::create([
                'coa_id' => $coaMap[$t['coa_code']],
                'date' => $t['date'],
                'desc' => $t['desc'],
                'debit' => $t['debit'] ?? 0,
                'credit' => $t['credit'] ?? 0,
            ]);
        }

        $this->command->info('Transactions seeded successfully.');
    }
}
