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
        Schema::create('emi_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('EMI_ID');
            $table->decimal('EMI_Amount');
            $table->date('EMI_Date');
            $table->boolean('EMI_Status')->default('false');
            $table->string('PaymentType')->nullable();
            $table->string('TransactionID')->nullable();
            $table->timestamps();
            $table->foreign('EMI_ID')->references('id')->on('emi_infos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emi_installments');
    }
};
