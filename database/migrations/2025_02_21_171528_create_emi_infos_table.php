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
        Schema::create('emi_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('PlotPrincipalAmount');
            $table->date('EmiDate'); 
            $table->integer('LoanTenure');
            $table->decimal('InterestRate',5,2); 
            $table->decimal('EmiAmount',20,2);
            $table->decimal('InterestRateAmount',10,2);
            $table->decimal('MonthInstallment',10,2);
            $table->decimal('TotalInterestAmount', 10,2);
            $table->enum('payment_plan_type', ['FORECLOSURE', 'FLEXI', 'MONTHLYEMI'])->default('MONTHLYEMI');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emi_infos');
    }
};
