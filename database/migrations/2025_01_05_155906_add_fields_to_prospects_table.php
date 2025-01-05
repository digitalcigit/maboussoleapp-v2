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
        Schema::table('prospects', function (Blueprint $table) {
            // Informations personnelles
            if (!Schema::hasColumn('prospects', 'birth_date')) {
                $table->date('birth_date')->nullable();
            }
            if (!Schema::hasColumn('prospects', 'profession')) {
                $table->string('profession')->nullable();
            }
            if (!Schema::hasColumn('prospects', 'education_level')) {
                $table->string('education_level')->nullable();
            }

            // Situation professionnelle
            if (!Schema::hasColumn('prospects', 'current_location')) {
                $table->string('current_location')->nullable();
            }
            if (!Schema::hasColumn('prospects', 'current_field')) {
                $table->string('current_field')->nullable();
            }
            if (!Schema::hasColumn('prospects', 'desired_field')) {
                $table->string('desired_field')->nullable();
            }
            if (!Schema::hasColumn('prospects', 'desired_destination')) {
                $table->string('desired_destination')->nullable();
            }

            // Contact d'urgence
            if (!Schema::hasColumn('prospects', 'emergency_contact')) {
                $table->json('emergency_contact')->nullable();
            }

            // Suivi commercial
            if (!Schema::hasColumn('prospects', 'commercial_code')) {
                $table->string('commercial_code')->nullable();
            }
            if (!Schema::hasColumn('prospects', 'partner_id')) {
                $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('prospects', 'analysis_deadline')) {
                $table->timestamp('analysis_deadline')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn([
                'birth_date',
                'profession',
                'education_level',
                'current_location',
                'current_field',
                'desired_field',
                'desired_destination',
                'emergency_contact',
                'commercial_code',
                'partner_id',
                'analysis_deadline',
            ]);
        });
    }
};
