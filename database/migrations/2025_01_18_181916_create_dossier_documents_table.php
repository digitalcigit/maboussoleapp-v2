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
        Schema::create('dossier_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers')->onDelete('cascade');
            $table->unsignedTinyInteger('step_number'); // 1,2,3,4
            $table->string('document_type', 50); // garant_financier, attestation_travail, etc.
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('uploaded_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['dossier_id', 'step_number']);
            $table->index('document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dossier_documents');
    }
};
