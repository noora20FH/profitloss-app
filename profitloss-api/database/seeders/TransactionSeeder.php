<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
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
        Transaction::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ambil ID COA yang sudah ada
        $coaMap = ChartOfAccount::pluck('id', 'code')->toArray();

        $transactions = [
            // JANUARI 2022
            ['date' => '2022-01-01', 'coa_id' => '401', 'desc' => 'Gaji Di Perusahaan A', 'credit' => 5000000],
            ['date' => '2022-01-02', 'coa_id' => '402', 'desc' => 'Gaji Ketum', 'credit' => 7000000],
            ['date' => '2022-01-05', 'coa_id' => '403', 'desc' => 'Untung Saham Januari', 'credit' => 5500000], 
            ['date' => '2022-01-10', 'coa_id' => '602', 'desc' => 'Bensin Anak', 'debit' => 25000],
            ['date' => '2022-01-15', 'coa_id' => '601', 'desc' => 'Biaya Sekolah Anak Bulanan', 'debit' => 500000], 
            ['date' => '2022-01-18', 'coa_id' => '603', 'desc' => 'Parkir Mall', 'debit' => 25000],
            ['date' => '2022-01-20', 'coa_id' => '604', 'desc' => 'Makan Siang Kantor', 'debit' => 50000],
            ['date' => '2022-01-25', 'coa_id' => '605', 'desc' => 'Belanja Bulanan', 'debit' => 100000], 
            // FEBRUARI id
            ['date' => '2022-02-01', 'coa_id' => '401', 'desc' => 'Gaji Di Perusahaan A', 'credit' => 5000000],
            ['date' => '2022-02-02', 'coa_id' => '402', 'desc' => 'Gaji Ketum', 'credit' => 7000000],
            ['date' => '2022-02-05', 'coa_id' => '403', 'desc' => 'Untung Saham Februari', 'credit' => 6000000], 
            ['date' => '2022-02-10', 'coa_id' => '601', 'desc' => 'Biaya Sekolah Tambahan', 'debit' => 3500000], 
            ['date' => '2022-02-15', 'coa_id' => '602', 'desc' => 'Bensin Pribadi', 'debit' => 200000],
            ['date' => '2022-02-20', 'coa_id' => '603', 'desc' => 'Parkir Bandara', 'debit' => 50000], 
            ['date' => '2022-02-25', 'coa_id' => '604', 'desc' => 'Makan Malam Keluarga', 'debit' => 150000],
            ['date' => '2022-02-28', 'coa_id' => '605', 'desc' => 'Belanja Mingguan', 'debit' => 150000], 
            // MARET id
            ['date' => '2022-03-01', 'coa_id' => '401', 'desc' => 'Gaji Di Perusahaan A', 'credit' => 5000000],
            ['date' => '2022-03-02', 'coa_id' => '402', 'desc' => 'Gaji Ketum', 'credit' => 7000000], 
            ['date' => '2022-03-05', 'coa_id' => '403', 'desc' => 'Untung Saham Maret', 'credit' => 3500000], 
            ['date' => '2022-03-10', 'coa_id' => '601', 'desc' => 'Biaya Sekolah Tahunan', 'debit' => 4500000], 
            ['date' => '2022-03-15', 'coa_id' => '602', 'desc' => 'Bensin Luar Kota', 'debit' => 200000],
            ['date' => '2022-03-20', 'coa_id' => '603', 'desc' => 'Parkir Rumah Sakit', 'debit' => 25000], 
            ['date' => '2022-03-25', 'coa_id' => '604', 'desc' => 'Makan Siang Klien', 'debit' => 100000],
            ['date' => '2022-03-28', 'coa_id' => '605', 'desc' => 'Beli Sembako', 'debit' => 75000], 
        ];

        foreach ($transactions as $t) {
            $coaId = $coaMap[$t['coa_id']] ?? null;

            if ($coaId) {
                Transaction::create([
                    'coa_id' => $coaId,
                    'date' => $t['date'],
                    'desc' => $t['desc'],
                    'debit' => $t['debit'] ?? 0,
                    'credit' => $t['credit'] ?? 0,
                ]);
            } else {
                $this->command->error('COA Kode ' . $t['coa_code'] . ' tidak ditemukan. Lewati transaksi pada ' . $t['date']);
            }
        }

        $this->command->info('Transactions seeded successfully.');
    }
}