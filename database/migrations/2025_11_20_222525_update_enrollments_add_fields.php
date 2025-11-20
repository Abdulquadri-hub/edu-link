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
        Schema::table('enrollments', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollments', 'frequency')) {
                $table->enum('frequency', ['3x', '5x'])->nullable()->default('3x')->after('course_id');
            }

            if (!Schema::hasColumn('enrollments', 'price')) {
                $table->decimal('price', 8, 2)->nullable()->after('frequency');
            }

            if (!Schema::hasColumn('enrollments', 'notes')) {
                $table->json('notes')->nullable()->after('price');
            }

            if (!Schema::hasColumn('enrollments', 'status')) {
                $table->enum('status', ['pending_payment','active','cancelled','completed'])->default('pending_payment')->after('notes');
            }

            if (!Schema::hasColumn('enrollments', 'academic_level_id')) {
                $table->foreignId('academic_level_id')->nullable()->constrained('academic_levels')->onDelete('set null')->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('enrollments', 'frequency')) {
                $table->dropColumn('frequency');
            }

            if (Schema::hasColumn('enrollments', 'price')) {
                $table->dropColumn('price');
            }

            if (Schema::hasColumn('enrollments', 'notes')) {
                $table->dropColumn('notes');
            }

            if (Schema::hasColumn('enrollments', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('enrollments', 'academic_level_id')) {
                $table->dropForeign(['academic_level_id']);
                $table->dropColumn('academic_level_id');
            }
        });
    }
};
