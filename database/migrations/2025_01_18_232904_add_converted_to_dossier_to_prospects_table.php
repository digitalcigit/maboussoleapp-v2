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
            $table->boolean('converted_to_dossier')->default(false);
            $table->timestamp('converted_at')->nullable();
            $table->string('dossier_reference')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn([
                'converted_to_dossier',
                'converted_at',
                'dossier_reference'
            ]);
        });
    }
};
