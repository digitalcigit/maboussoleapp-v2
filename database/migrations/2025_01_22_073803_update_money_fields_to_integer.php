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
        Schema::table('dossiers', function (Blueprint $table) {
            $table->integer('tuition_total_amount')->change();
            $table->integer('down_payment_amount')->change();
            $table->integer('tuition_paid_amount')->change();
            $table->integer('agency_payment_amount')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->decimal('tuition_total_amount', 10, 2)->change();
            $table->decimal('down_payment_amount', 10, 2)->change();
            $table->decimal('tuition_paid_amount', 10, 2)->change();
            $table->decimal('agency_payment_amount', 10, 2)->change();
        });
    }
};
