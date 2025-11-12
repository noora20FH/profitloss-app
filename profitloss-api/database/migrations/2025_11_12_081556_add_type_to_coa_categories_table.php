<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coa_categories', function (Blueprint $table) {
            // Tambahkan kolom 'type'. Kita batasi pilihannya hanya 'Income' atau 'Expense' untuk P/L
            $table->enum('type', ['Income', 'Expense', 'Asset', 'Liability', 'Equity'])
                  ->after('name')
                  ->default('Expense'); // Beri default jika diperlukan
        });
    }

    public function down(): void
    {
        Schema::table('coa_categories', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
