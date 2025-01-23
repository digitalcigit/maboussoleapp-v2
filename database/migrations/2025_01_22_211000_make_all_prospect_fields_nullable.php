<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->string('first_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->date('birth_date')->nullable()->change();
            $table->string('profession')->nullable()->change();
            $table->string('education_level')->nullable()->change();
            $table->string('desired_field')->nullable()->change();
            $table->string('desired_destination')->nullable()->change();
            $table->json('emergency_contact')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->date('birth_date')->nullable(false)->change();
            $table->string('profession')->nullable(false)->change();
            $table->string('education_level')->nullable(false)->change();
            $table->string('desired_field')->nullable(false)->change();
            $table->string('desired_destination')->nullable(false)->change();
            $table->json('emergency_contact')->nullable(false)->change();
        });
    }
};
