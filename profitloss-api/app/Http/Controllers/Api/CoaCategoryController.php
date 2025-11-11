<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoaCategory;
use Illuminate\Http\Request;

class CoaCategoryController extends Controller
{
    /**
     * Tampilkan daftar Kategori COA. (Read)
     */
    public function index()
    {
        return response()->json(CoaCategory::all());
    }

    /**
     * Simpan Kategori COA baru. (Create)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:coa_categories,name',
        ]);

        $category = CoaCategory::create($validated);
        return response()->json($category, 201); // 201 Created
    }

    /**
     * Perbarui Kategori COA tertentu. (Update)
     */
    public function update(Request $request, CoaCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:coa_categories,name,' . $category->id,
        ]);

        $category->update($validated);
        return response()->json($category);
    }

    // Fungsi show dan destroy (Delete) juga dapat ditambahkan jika diperlukan.
}