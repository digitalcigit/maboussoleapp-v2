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
            // Montant des frais d'admission
            $table->decimal('admission_fees', 10, 2)->nullable();
            $table->timestamp('admission_fees_paid_at')->nullable();
            
            // Destination (école/université)
            $table->string('school_name')->nullable();
            $table->string('school_program')->nullable(); // Programme/Filière
            $table->string('school_country')->nullable();
            $table->text('school_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn([
                'admission_fees',
                'admission_fees_paid_at',
                'school_name',
                'school_program',
                'school_country',
                'school_notes',
            ]);
        });
    }
};
