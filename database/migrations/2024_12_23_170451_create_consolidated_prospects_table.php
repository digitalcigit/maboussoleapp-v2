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
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->date('birth_date');
            $table->string('profession')->nullable();
            $table->string('education_level');
            $table->string('desired_field')->nullable();
            $table->string('desired_destination')->nullable();
            $table->json('emergency_contact')->nullable();
            $table->json('documents')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('partner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('commercial_code')->nullable();
            $table->timestamp('analysis_deadline')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
