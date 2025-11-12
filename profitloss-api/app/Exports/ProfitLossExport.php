<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProfitLossExport implements FromCollection, WithHeadings
{
    protected $startDate;
    protected $endDate;
    protected $reportData;
    protected $dynamicMonths;

    public function __construct($startDate, $endDate, $reportData, $dynamicMonths)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->reportData = $reportData;
        $this->dynamicMonths = $dynamicMonths;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
public function collection()
{
    $exportCollection = new Collection();
    $groupedData = $this->reportData; // Data sudah dikelompokkan oleh controller

    $types = ['Income', 'Expense'];

    foreach ($types as $type) {
        if (!isset($groupedData[$type]) || empty($groupedData[$type])) continue;

        // 1. Tambahkan Baris Header Tipe Akun
        $row = ['--- ' . strtoupper($type) . ' ---'];
        $exportCollection->push($row); // Baris yang hanya berisi nama Tipe

        $totalByType = [];

        // 2. Tambahkan Baris Data Kategori
        foreach ($groupedData[$type] as $cat) {
            $catRow = [$cat['name']];
            $rowTotal = 0;

            foreach ($this->dynamicMonths as $month) {
                $value = $cat['data_by_month'][$month] ?? 0;
                $catRow[] = $value;
                $rowTotal += $value;
                $totalByType[$month] = ($totalByType[$month] ?? 0) + $value;
            }
            $catRow[] = $rowTotal;
            $exportCollection->push($catRow);
        }

        // 3. Tambahkan Baris TOTAL TIPE
        $totalRow = ['TOTAL ' . strtoupper($type)];
        foreach ($this->dynamicMonths as $month) {
            $totalRow[] = $totalByType[$month] ?? 0;
        }
        $totalRow[] = array_sum($totalByType); // Total periode untuk tipe ini
        $exportCollection->push($totalRow);
    }

    // 4. Tambahkan Baris NET INCOME (opsional, bisa dihitung dari total Income & Expense)
    // Logika ini bisa kompleks tanpa akses langsung ke summary, tapi bisa dilakukan.

    return $exportCollection;
}

    /**
     * Menambahkan baris Judul (Header) di Excel.
     */
public function headings(): array
{
    $headers = ['Kategori COA'];
    // Format Bulan Excel
    foreach ($this->dynamicMonths as $monthYear) {
        $headers[] = date('M Y', strtotime($monthYear . '-01'));
    }
    $headers[] = 'Total Periode';
    return $headers;
}

    /**
     * Memetakan data dari collection ke baris Excel.
     */
    public function map($row): array
    {
        $mappedRow = [$row['category_name']];

        // Tambahkan nilai per bulan
        foreach ($this->dynamicMonths as $monthYear) {
            $mappedRow[] = $row['data_by_month'][$monthYear];
        }

        $mappedRow[] = $row['total_periode'];
        return $mappedRow;
    }
}
