<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mise à jour directe de la structure et des données
        DB::unprepared("
            ALTER TABLE activities MODIFY status VARCHAR(255);
            UPDATE activities SET status = CASE
                WHEN status = 'en_attente' THEN 'pending'
                WHEN status = 'en_cours' THEN 'in_progress'
                WHEN status = 'termine' THEN 'completed'
                WHEN status = 'annule' THEN 'cancelled'
                ELSE status
            END;
            ALTER TABLE activities MODIFY status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restauration directe de la structure et des données
        DB::unprepared("
            ALTER TABLE activities MODIFY status VARCHAR(255);
            UPDATE activities SET status = CASE
                WHEN status = 'pending' THEN 'en_attente'
                WHEN status = 'in_progress' THEN 'en_cours'
                WHEN status = 'completed' THEN 'termine'
                WHEN status = 'cancelled' THEN 'annule'
                ELSE status
            END;
            ALTER TABLE activities MODIFY status ENUM('en_attente', 'en_cours', 'termine', 'annule') NOT NULL DEFAULT 'en_attente';
        ");
    }
};
