<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Trigger for prospect deadline notifications
            DB::unprepared('
                CREATE TRIGGER check_prospect_deadline
                AFTER UPDATE ON prospects
                FOR EACH ROW
                BEGIN
                    IF NEW.analysis_deadline < NOW() + INTERVAL 24 HOUR THEN
                        INSERT INTO notifications (id, type, notifiable_type, notifiable_id, data, created_at, updated_at)
                        VALUES (
                            UUID(),
                            "App\\Notifications\\ProspectDeadlineApproaching",
                            "prospect",
                            NEW.id,
                            JSON_OBJECT(
                                "prospect_id", NEW.id,
                                "reference_number", NEW.reference_number,
                                "deadline", NEW.analysis_deadline
                            ),
                            NOW(),
                            NOW()
                        );
                    END IF;
                END;
            ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop trigger first
        DB::unprepared('DROP TRIGGER IF EXISTS check_prospect_deadline');

        Schema::dropIfExists('notifications');
    }
};
