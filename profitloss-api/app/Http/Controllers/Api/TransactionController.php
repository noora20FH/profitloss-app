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
        // Load relasi COA untuk tampilan tabel
        return response()->json(Transaction::with('coa')->orderBy('date', 'desc')->get());
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