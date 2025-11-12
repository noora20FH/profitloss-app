<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CoaCategory; // Untuk mengambil nama kategori
use App\Exports\ProfitLossExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Transaction;

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
                'coa_categories.type as category_type',
                // Mengelompokkan berdasarkan bulan dan tahun
                DB::raw('YEAR(transactions.date) as year'),
                DB::raw('MONTH(transactions.date) as month'),
                // Menjumlahkan Debit dan Kredit
                DB::raw('SUM(transactions.debit) as total_debit'),
                DB::raw('SUM(transactions.credit) as total_credit')
            )
            ->whereBetween('transactions.date', [$startDate, $endDate])
            ->groupBy('coa_categories.id', 'coa_categories.name', 'coa_categories.type', DB::raw('YEAR(transactions.date)'), DB::raw('MONTH(transactions.date)'))
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
        $reportDataByMonth = [];
        $totalIncome = 0;
        $totalExpense = 0;

        // Grouping data ke dalam struktur yang lebih baik
        $groupedByTypeAndMonth = [];

        foreach ($results as $row) {

            // 🎯 PERBAIKAN 1: Gunakan null coalescing untuk menjamin property ada.
            // Jika row->category_type undefined/null, default ke 'Unknown'
            $categoryType = $row->category_type ?? 'Unknown';

            // Net Value = (Debit) - (Kredit)
            $netValue = $row->total_debit - $row->total_credit;

            // 1. Tentukan Nilai yang akan ditampilkan (Absolute Value)
            $displayValue = 0;
            // Ganti semua $row->category_type dengan $categoryType
            if ($categoryType === 'Income') {
                // Untuk Income, nilai yang relevan adalah Credit (Pemasukan).
                $displayValue = $row->total_credit;
                $totalIncome += $row->total_credit;
            } elseif ($categoryType === 'Expense') {
                // Untuk Expense, nilai yang relevan adalah Debit (Beban).
                $displayValue = $row->total_debit;
                $totalExpense += $row->total_debit;
            } else {
                // Jika tipenya Unknown (misalnya Aset/Liabilitas/Equity)
                // Baris ini TIDAK seharusnya tampil di P/L. Abaikan di perhitungan total P/L.
                // Anda mungkin perlu memfilter ini di query jika tidak ingin masuk.
                continue;
            }

            // 2. Kumpulkan data berdasarkan Tipe Akun dan Bulan
            $monthYear = $row->year . '-' . str_pad($row->month, 2, '0', STR_PAD_LEFT);
            $type = $categoryType; // Gunakan variabel yang aman

            if (!isset($groupedByTypeAndMonth[$type])) {
                $groupedByTypeAndMonth[$type] = [];
            }
            if (!isset($groupedByTypeAndMonth[$type][$row->category_id])) {
                $groupedByTypeAndMonth[$type][$row->category_id] = [
                    'id' => $row->category_id,
                    'name' => $row->category_name,
                    'type' => $type,
                    'data_by_month' => []
                ];
            }

            // Simpan nilai tampil (Income/Expense Value) per bulan
            $groupedByTypeAndMonth[$type][$row->category_id]['data_by_month'][$monthYear] = $displayValue;
        }

        $netIncome = $totalIncome - $totalExpense;

        return [
            // 🎯 Kunci 'data' sekarang berisi data yang dikelompokkan berdasarkan Tipe Akun
            'grouped_data' => $groupedByTypeAndMonth,
            'total_income' => (float) $totalIncome,
            'total_expense' => (float) $totalExpense,
            'net_income' => (float) $netIncome,
        ];
    }
    public function exportProfitLoss(Request $request)
    {
        // Lakukan validasi yang sama
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        // Dapatkan data agregasi MENTAH dari Query Agregasi (D1.2)
        $results = DB::table('transactions')
            ->join('chart_of_accounts', 'transactions.coa_id', '=', 'chart_of_accounts.id')
            ->join('coa_categories', 'chart_of_accounts.category_id', '=', 'coa_categories.id')
            ->select(
                'coa_categories.id as category_id',
                'coa_categories.name as category_name',
                'coa_categories.type as category_type', // 🎯 PASTIKAN BARIS INI ADA
                DB::raw('YEAR(transactions.date) as year'),
                DB::raw('MONTH(transactions.date) as month'),
                DB::raw('SUM(transactions.debit) as total_debit'),
                DB::raw('SUM(transactions.credit) as total_credit')
            )
            ->whereBetween('transactions.date', [$startDate, $endDate])
            // 🎯 PASTIKAN category_type JUGA ADA DI GROUP BY
            ->groupBy(
                'coa_categories.id',
                'coa_categories.name',
                'coa_categories.type', // 🎯 PASTIKAN BARIS INI ADA
                DB::raw('YEAR(transactions.date)'),
                DB::raw('MONTH(transactions.date)')
            )
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Lanjutkan memanggil formatProfitLossData
        $reportData = $this->formatProfitLossData($results);

        // Ambil bulan dinamis untuk header Excel
        // 🎯 PERBAIKAN 2: Mengambil daftar bulan unik dari struktur data yang bersarang
        $dynamicMonths = [];

        foreach ($reportData['grouped_data'] as $type => $categories) {
            foreach ($categories as $category) {
                foreach (array_keys($category['data_by_month']) as $month) {
                    $dynamicMonths[] = $month;
                }
            }
        }
        // Hapus duplikat dan urutkan
        $dynamicMonths = array_unique($dynamicMonths);
        sort($dynamicMonths);


        $fileName = 'Laba_Rugi_' . $startDate . '_to_' . $endDate . '.xlsx';

        // Panggil Export Class
        return Excel::download(
            new ProfitLossExport($startDate, $endDate, $reportData['grouped_data'], $dynamicMonths),
            $fileName
        );
    }


    /**
     * Mengembalikan ringkasan total income/expense untuk bulan berjalan.
     * GET /api/reports/summary/month
     */
   public function getAllTransactions()
    {
        // Ambil semua transaksi, diurutkan dari terbaru ke terlama
        // Menggunakan with(['coa.category']) untuk Eager Loading
        $transactions = Transaction::with(['coa.category'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Transformasi data untuk penggunaan di frontend
        return response()->json($transactions->map(function ($tx) {

            // Menggunakan optional() untuk menghindari error jika relasi coa atau category null
            $categoryType = optional(optional($tx->coa)->category)->type ?? 'Unknown';

            // Tentukan jumlah: jika Debit > 0, gunakan Debit; jika tidak, gunakan Credit
            // Asumsi kolom debit dan credit ada di tabel transactions
            $amount = $tx->debit > 0 ? $tx->debit : $tx->credit;

            return [
                'id' => $tx->id,
                // Format tanggal agar mudah dibaca di frontend (ex: 12 Nov 2025)
                'date' => $tx->date->format('d M Y'),
                'description' => $tx->description,
                'type' => $categoryType, // 'Income', 'Expense', atau 'Unknown'
                'amount' => $amount,
                // Gunakan optional() untuk nama COA
                'coa_name' => optional($tx->coa)->name ?? 'N/A',
            ];
        }));
    }
}
