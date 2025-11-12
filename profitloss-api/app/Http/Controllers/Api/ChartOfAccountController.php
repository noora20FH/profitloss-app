<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    /**
     * Tampilkan daftar COA. (Read)
     */
    public function index()
    {
        // Load relasi category untuk memudahkan frontend
        return response()->json(ChartOfAccount::with('category')->get());
    }

    /**
     * Simpan COA baru. (Create)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:coa_categories,id', // Validasi FK ke tabel kategori
            'code' => 'required|string|max:10|unique:chart_of_accounts,code',
            'name' => 'required|string|max:255',
        ]);

        $coa = ChartOfAccount::create($validated);
        return response()->json($coa, 201);
    }

    /**
     * Perbarui COA tertentu. (Update)
     */
    public function update(Request $request, ChartOfAccount $coa)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:coa_categories,id',
            'code' => 'required|string|max:10|unique:chart_of_accounts,code,' . $coa->id,
            'name' => 'required|string|max:255',
        ]);

        $coa->update($validated);
        return response()->json($coa);
    }
    public function destroy(ChartOfAccount $coa)
    {
        // Cek relasi: Pastikan COA tidak digunakan dalam Transaksi
        if ($coa->transactions()->exists()) {
            return response()->json([
                'message' => 'Gagal menghapus. Akun COA ini sudah memiliki Transaksi terkait.'
            ], 409); // 409 Conflict
        }

        $coa->delete();
        return response()->json(null, 204);
    }
}
