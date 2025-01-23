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
            // Informations personnelles
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('profession')->nullable();
            $table->string('education_level')->nullable();
            $table->string('desired_field')->nullable();
            $table->string('desired_destination')->nullable();
            
            // Contact d'urgence
            $table->json('emergency_contact')->nullable();
            
            // Documents
            $table->json('documents')->nullable();
            
            // Autres informations
            $table->string('commercial_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'email',
                'phone',
                'birth_date',
                'profession',
                'education_level',
                'desired_field',
                'desired_destination',
                'emergency_contact',
                'documents',
                'commercial_code'
            ]);
        });
    }
};
