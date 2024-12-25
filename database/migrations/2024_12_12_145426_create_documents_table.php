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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentable');
            $table->string('name');
            $table->enum('type', ['passport', 'cv', 'diploma', 'other']);
            $table->string('path');
            $table->bigInteger('size');
            $table->enum('status', ['pending', 'validated', 'rejected']);
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validation_date')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
