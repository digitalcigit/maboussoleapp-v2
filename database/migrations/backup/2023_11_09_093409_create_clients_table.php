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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->nullable()->constrained()->nullOnDelete();
            $table->string('client_number')->unique();
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
            $table->enum('status', [
                'active',
                'inactive',
                'pending',
                'archived',
            ])->default('active');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('commercial_code')->nullable();
            $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_action_at')->nullable();
            $table->timestamp('contract_start_date')->nullable();
            $table->timestamp('contract_end_date')->nullable();
            $table->string('passport_number')->nullable();
            $table->timestamp('passport_expiry')->nullable();
            $table->enum('visa_status', ['not_started', 'in_progress', 'obtained', 'rejected'])->nullable();
            $table->json('travel_preferences')->nullable();
            $table->enum('payment_status', ['pending', 'partial', 'completed'])->default('pending');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
