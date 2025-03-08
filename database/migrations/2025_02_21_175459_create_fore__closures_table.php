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
        Schema::create('fore__closures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('EMI_ID');
            $table->decimal('ClosureAmount',15,2);
            $table->decimal('ChargesPercent',3,2);
            $table->decimal('ClosureCharges',10,2);
            $table->date('ClosureDate');
            $table->timestamps();

            $table->foreign('EMI_ID')->references('id')->on('emi_infos')->onDelete ('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fore__closures');
    }
};
