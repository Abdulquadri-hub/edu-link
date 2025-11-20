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
         Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('academic_level_id')
                  ->nullable()
                  ->after('level')
                  ->constrained('academic_levels')
                  ->nullOnDelete();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('academic_level_id')
                  ->nullable()
                  ->after('enrollment_status')
                  ->constrained('academic_levels')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['academic_level_id']);
            $table->dropColumn('academic_level_id');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['academic_level_id']);
            $table->dropColumn('academic_level_id');
        });
    }
};
