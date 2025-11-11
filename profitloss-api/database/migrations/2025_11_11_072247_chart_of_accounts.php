<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id(); // Primary Key: id
            
            // Foreign Key: category_id (links to coa_categories)
            $table->foreignId('category_id')->constrained('coa_categories')->onDelete('cascade');
            
            $table->string('code')->unique(); // COA Code (e.g., 401)
            $table->string('name'); // COA Name (e.g., Gaji Karyawan)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};