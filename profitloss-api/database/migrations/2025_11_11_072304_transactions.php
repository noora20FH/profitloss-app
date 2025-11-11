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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id(); // Primary Key: id
            
            // Foreign Key: coa_id (links to chart_of_accounts)
            $table->foreignId('coa_id')->constrained('chart_of_accounts')->onDelete('cascade');
            
            $table->date('date'); // Transaction Date
            $table->text('description')->nullable(); // Transaction Description
            
            // Decimal for monetary values (total digits, decimal places)
            $table->decimal('debit', 15, 2)->default(0); 
            $table->decimal('credit', 15, 2)->default(0); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};