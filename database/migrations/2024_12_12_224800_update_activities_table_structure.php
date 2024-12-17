<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, remove the problematic migration from the migrations table
        DB::table('migrations')
            ->where('migration', '2024_12_12_224500_add_subject_fields_to_activities_table')
            ->delete();

        // Then update the activities table
        Schema::table('activities', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('activities', 'title')) {
                $table->string('title')->after('id');
            }
            
            if (!Schema::hasColumn('activities', 'status')) {
                $table->enum('status', ['planifié', 'en_cours', 'terminé', 'annulé'])->after('type');
            }
            
            if (!Schema::hasColumn('activities', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable();
                $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('activities', 'prospect_id')) {
                $table->unsignedBigInteger('prospect_id')->nullable();
                $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Drop foreign keys first
            if (Schema::hasColumn('activities', 'client_id')) {
                $table->dropForeign(['client_id']);
            }
            if (Schema::hasColumn('activities', 'prospect_id')) {
                $table->dropForeign(['prospect_id']);
            }
            
            // Drop columns if they exist
            $columns = ['title', 'status', 'client_id', 'prospect_id'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('activities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Re-add the migration record
        DB::table('migrations')->insert([
            'migration' => '2024_12_12_224500_add_subject_fields_to_activities_table',
            'batch' => 1
        ]);
    }
};
