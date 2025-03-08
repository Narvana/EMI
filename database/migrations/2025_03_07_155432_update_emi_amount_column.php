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
        Schema::table('emi_installments', function (Blueprint $table) {
            //
                $table->decimal('EMI_Amount', 20, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emi_installments', function (Blueprint $table) {
            //
            $table->decimal('EMI_Amount')->change(); // Rollback to default
        });
    }
};
