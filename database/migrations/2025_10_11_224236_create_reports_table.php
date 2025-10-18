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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('report_type', [
                'student-progress',
                'student-attendance',
                'instructor-performance',
                'course-analytics',
                'parent-summary',
                'financial',
                'custom'
            ])->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('data')->nullable(); // Report data
            $table->string('file_path')->nullable(); // Generated PDF
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->dateTime('generated_at')->default(now());
            $table->timestamps();
            $table->softDeletes();

            $table->index(['report_type', 'generated_at']);
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
