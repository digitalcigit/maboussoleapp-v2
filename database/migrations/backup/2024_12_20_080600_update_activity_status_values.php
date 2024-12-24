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
        Schema::table('activities', function (Blueprint $table) {
            // Drop the existing status column
            $table->dropColumn('status');
        });

        Schema::table('activities', function (Blueprint $table) {
            // Recreate the status column with new values
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])
                ->default('planned')
                ->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Drop the new status column
            $table->dropColumn('status');
        });

        Schema::table('activities', function (Blueprint $table) {
            // Recreate the original status column
            $table->enum('status', ['planifié', 'en_cours', 'terminé', 'annulé'])
                ->default('planifié')
                ->after('type');
        });
    }
};
