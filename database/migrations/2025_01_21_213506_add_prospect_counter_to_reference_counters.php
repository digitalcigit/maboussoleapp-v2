<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter le compteur pour les prospects
        DB::table('reference_counters')->insert([
            'type' => 'prospect',
            'current_value' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer le compteur prospect
        DB::table('reference_counters')
            ->where('type', 'prospect')
            ->delete();
    }
};
