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
        Schema::create('reference_counters', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();  // 'dossier', etc.
            $table->integer('current_value')->default(0);
            $table->timestamps();
        });

        // Initialiser le compteur pour les dossiers
        DB::table('reference_counters')->insert([
            'type' => 'dossier',
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
        Schema::dropIfExists('reference_counters');
    }
};
