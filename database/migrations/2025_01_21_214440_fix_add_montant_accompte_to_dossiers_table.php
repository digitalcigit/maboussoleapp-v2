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
        if (!Schema::hasColumn('dossiers', 'montant_accompte')) {
            Schema::table('dossiers', function (Blueprint $table) {
                $table->decimal('montant_accompte', 15, 2)->nullable()->after('tuition_total_amount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn('montant_accompte');
        });
    }
};
