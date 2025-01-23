<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->decimal('agency_payment_amount', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn('agency_payment_amount');
        });
    }
};
