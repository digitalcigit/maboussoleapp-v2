<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove the problematic migration from the migrations table
        DB::table('migrations')
            ->where('migration', '2024_12_12_224500_add_subject_fields_to_activities_table')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the migration record
        DB::table('migrations')->insert([
            'migration' => '2024_12_12_224500_add_subject_fields_to_activities_table',
            'batch' => 1,
        ]);
    }
};
