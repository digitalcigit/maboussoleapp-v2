<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('profession')->nullable();
            $table->string('education_level')->nullable();
            $table->string('current_location')->nullable();
            $table->string('current_field')->nullable();
            $table->string('desired_field')->nullable();
            $table->string('desired_destination')->nullable();
            $table->json('emergency_contact')->nullable();
            $table->enum('status', [
                'nouveau',
                'en_cours',
                'qualifie',
                'non_qualifie',
                'converti',
                'annule',
            ])->default('nouveau');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->string('commercial_code')->nullable();
            $table->foreignId('partner_id')->nullable()->constrained('users');
            $table->timestamp('last_action_at')->nullable();
            $table->timestamp('analysis_deadline')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('last_action_at');
            $table->index('analysis_deadline');
        });
    }

    public function down()
    {
        Schema::dropIfExists('prospects');
    }
};
