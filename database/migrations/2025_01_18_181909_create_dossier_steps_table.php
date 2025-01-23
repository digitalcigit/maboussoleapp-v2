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
        Schema::create('dossier_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers')->onDelete('cascade');
            $table->unsignedTinyInteger('step_number'); // 1,2,3,4
            $table->string('status', 50);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Pour stocker des données spécifiques à l'étape
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['dossier_id', 'step_number']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dossier_steps');
    }
};
