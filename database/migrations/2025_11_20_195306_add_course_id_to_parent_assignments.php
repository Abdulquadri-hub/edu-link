<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('parent_assignments')) {
            Schema::table('parent_assignments', function (Blueprint $table) {
                if (!Schema::hasColumn('parent_assignments', 'course_id')) {
                    $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('set null');
                    $table->index('course_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('parent_assignments')) {
            Schema::table('parent_assignments', function (Blueprint $table) {
                if (Schema::hasColumn('parent_assignments', 'course_id')) {
                    $table->dropForeign(['course_id']);
                    $table->dropIndex(['course_id']);
                    $table->dropColumn('course_id');
                }
            });
        }
    }
};
