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
        Schema::create('dossiers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('prospect_id')->constrained('prospects')->onDelete('cascade');
            $table->unsignedTinyInteger('current_step')->default(1); // 1,2,3,4
            $table->string('current_status', 50)->default('en_attente');
            $table->text('notes')->nullable();
            $table->timestamp('last_action_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['current_step', 'current_status']);
            $table->index('last_action_at');
            $table->enum('dossier_state', ['prospect', 'client', 'client_visa'])->default('prospect');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dossiers');
    }
};
