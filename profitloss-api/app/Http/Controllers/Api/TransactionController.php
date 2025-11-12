<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Tampilkan daftar Transaksi. (Read)
     */
public function index()
    {
        // Ambil semua transaksi, diurutkan dari terbaru ke terlama
        // Menggunakan with(['coa.category']) untuk Eager Loading
        $transactions = Transaction::with(['coa.category'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Transformasi data untuk penggunaan di frontend (memastikan format yang dibutuhkan Nuxt tersedia)
        return response()->json($transactions->map(function ($tx) {

            // Menggunakan optional() untuk menghindari error jika relasi coa atau category null
            $categoryType = optional(optional($tx->coa)->category)->type ?? 'Unknown';

            // Tentukan jumlah: jika Debit > 0, gunakan Debit; jika tidak, gunakan Credit
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

    /**
     * Simpan Transaksi baru: POST /api/transactions. (Create)
     */
    public function store(Request $request)
    {
        // --- Validasi Data ---
        $validated = $request->validate([
            // Memastikan coa_id ada di tabel chart_of_accounts
            'coa_id' => 'required|integer|exists:chart_of_accounts,id',
            'date' => 'required|date',
            'desc' => 'required|string|max:255',
            // Memastikan salah satu dari debit atau credit diisi, dan keduanya adalah angka
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
        ]);

        // Opsional: Validasi untuk memastikan hanya satu sisi (Debit atau Kredit) yang diisi
        if (
            (empty($validated['debit']) && empty($validated['credit'])) || // Keduanya kosong
            (!empty($validated['debit']) && !empty($validated['credit'])) // Keduanya terisi
        ) {
            return response()->json([
                'message' => 'Hanya satu sisi (debit atau credit) yang harus diisi.'
            ], 422); // 422 Unprocessable Entity
        }

        // --- Proses Penyimpanan ---
        $transaction = Transaction::create($validated);

        return response()->json($transaction, 201); // 201 Created
    }

    /**
     * Perbarui Transaksi tertentu. (Update)
     */
    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'coa_id' => 'required|integer|exists:chart_of_accounts,id',
            'date' => 'required|date',
            'desc' => 'required|string|max:255',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
        ]);

        if (
            (empty($validated['debit']) && empty($validated['credit'])) ||
            (!empty($validated['debit']) && !empty($validated['credit']))
        ) {
            return response()->json([
                'message' => 'Hanya satu sisi (debit atau credit) yang harus diisi.'
            ], 422);
        }

        $transaction->update($validated);
        return response()->json($transaction);
    }
}
