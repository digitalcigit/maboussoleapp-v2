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
            $table->string('email');
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('profession')->nullable();
            $table->string('education_level')->nullable();
            $table->string('current_location')->nullable();
            $table->string('current_field')->nullable();
            $table->string('desired_field')->nullable();
            $table->string('desired_destination')->nullable();
            $table->json('emergency_contact')->nullable();
            $table->enum('status', ['new', 'analyzing', 'qualified', 'converted', 'rejected'])->default('new');
            $table->string('source')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('commercial_code')->nullable();
            $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_action_at')->nullable();
            $table->timestamp('analysis_deadline')->nullable();
            $table->timestamps();
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
