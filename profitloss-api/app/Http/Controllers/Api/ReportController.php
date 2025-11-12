<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CoaCategory; // Untuk mengambil nama kategori

class ReportController extends Controller
{
    /**
     * D1.1 & D1.2: Menghasilkan Laporan Profit/Loss.
     * GET /api/reports/profitloss?start_date=...&end_date=...
     */
    public function profitLoss(Request $request)
    {
        // --- 1. Validasi Input ---
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        // --- 2. Query Agregasi (D1.2) ---
        // Menggunakan JOIN dan DB::raw untuk mengagregasi Debit/Kredit per Kategori COA dalam rentang tanggal.
        $results = DB::table('transactions')
            ->join('chart_of_accounts', 'transactions.coa_id', '=', 'chart_of_accounts.id')
            ->join('coa_categories', 'chart_of_accounts.category_id', '=', 'coa_categories.id')
            ->select(
                'coa_categories.id as category_id',
                'coa_categories.name as category_name',
                // Mengelompokkan berdasarkan bulan dan tahun
                DB::raw('YEAR(transactions.date) as year'),
                DB::raw('MONTH(transactions.date) as month'),
                // Menjumlahkan Debit dan Kredit
                DB::raw('SUM(transactions.debit) as total_debit'),
                DB::raw('SUM(transactions.credit) as total_credit')
            )
            ->whereBetween('transactions.date', [$startDate, $endDate])
            ->groupBy('coa_categories.id', 'coa_categories.name', DB::raw('YEAR(transactions.date)'), DB::raw('MONTH(transactions.date)'))
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // --- 3. Pengolahan Data dan Perhitungan Akhir (D1.3) ---

        $reportData = $this->formatProfitLossData($results);

        return response()->json([
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'data' => $reportData['grouped_data'],
            'summary' => [
                'total_income' => $reportData['total_income'],
                'total_expense' => $reportData['total_expense'],
                'net_income' => $reportData['net_income'],
            ]
        ]);
    }

    /**
     * D1.3: Olah data hasil kueri menjadi format yang terstruktur.
     */
    private function formatProfitLossData($results)
    {
        $groupedData = [];
        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($results as $row) {
            $monthYear = $row->year . '-' . str_pad($row->month, 2, '0', STR_PAD_LEFT);

            // Inisialisasi jika bulan/tahun belum ada
            if (!isset($groupedData[$monthYear])) {
                $groupedData[$monthYear] = [
                    'month_year' => $monthYear,
                    'categories' => [],
                ];
            }

            // Hitung total nilai kategori (Debit - Kredit, atau sebaliknya tergantung nature)
            // Untuk laporan P/L, kita asumsikan debit/credit mewakili perubahan pada COA.
            // Di sini, kita akan menyajikan total nilai per kategori sebagai penjumlahan:
            $netValue = $row->total_debit - $row->total_credit;

            // NOTE PENTING: Untuk Laporan Laba Rugi yang benar, Anda harus mengelompokkan
            // Kategori ke dalam Tipe "Pemasukan (Income)" atau "Beban (Expense)".
            // Karena kita tidak memiliki kolom 'type' di kategori, kita asumsikan
            // semua yang memiliki netValue positif adalah INCOME dan negatif adalah EXPENSE (ini SANGAT SIMPLISTIK).

            // Solusi Lebih Baik (jika Anda memiliki kolom TIPE di coa_categories):
            // $type = $row->category_type; // 'Income' atau 'Expense'

            // Dalam contoh ini, kita hanya menyajikan data agregat dan mengelompokkan
            // Total Income/Expense berdasarkan saldo.

            $groupedData[$monthYear]['categories'][] = [
                'category_id' => $row->category_id,
                'category_name' => $row->category_name,
                'total_debit' => (float) $row->total_debit,
                'total_credit' => (float) $row->total_credit,
                'net_value' => (float) $netValue,
            ];

            // Agregasi Total Income/Expense untuk ringkasan akhir
            if ($netValue > 0) {
                // Asumsi: Net Debit (Debit > Kredit) mewakili pemasukan atau peningkatan aset/penurunan utang
                // Dalam konteks P/L sederhana, ini bisa dihitung sebagai Net Income (Pendapatan - Beban)
                // Kita gunakan logika sederhana: total debit (pendapatan) vs total kredit (beban)
                $totalIncome += $row->total_debit;
                $totalExpense += $row->total_credit;

            } else {
                // Jika netValue negatif
                $totalIncome += $row->total_debit;
                $totalExpense += $row->total_credit;
            }
        }

        // Perhitungan Akhir
        $netIncome = $totalIncome - $totalExpense;

        // Reset indeks array agar Nuxt lebih mudah mengonsumsi (dari array asosiatif ke array of objects)
        $finalGroupedData = array_values($groupedData);

        return [
            'grouped_data' => $finalGroupedData,
            'total_income' => (float) $totalIncome,
            'total_expense' => (float) $totalExpense,
            'net_income' => (float) $netIncome,
        ];
    }
}
