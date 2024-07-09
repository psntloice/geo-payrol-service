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
        Schema::create('earnings', function (Blueprint $table) {
            $table->id('earningID');
            $table->string('employeeID');
            $table->unsignedBigInteger('payPeriodID')->default(0);
            $table->string('earningType');
            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamps();

            // Index and foreign key constraint to reference employees
            // No foreign key constraint because it's across different microservices
            $table->foreign('payPeriodID')->references('payPeriodID')->on('pay_periods')->onUpdate('cascade')->onDelete('set default');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('earnings');
    }
};
