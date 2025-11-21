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
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('from_level_id')->nullable()->constrained('academic_levels')->onDelete('set null');
            $table->foreignId('to_level_id')->constrained('academic_levels')->onDelete('cascade');
            $table->string('promotion_code')->unique(); // Generated code for tracking
            $table->enum('promotion_type', [
                'regular',      // Normal end-of-year promotion
                'skip',         // Skip a grade (advancement)
                'repeat',       // Repeat current grade
                'transfer',     // Transfer from another school
                'manual'        // Manual administrative promotion
            ])->default('regular');
            $table->string('academic_year')->nullable()->comment('e.g., 2023-2024');
            $table->decimal('final_gpa', 5, 2)->nullable()->comment('GPA at time of promotion');
            $table->text('promotion_notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('promoted_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('promotion_date')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('effective_date')->nullable()->comment('When promotion takes effect');
            $table->boolean('auto_update_enrollments')->default(true)->comment('Update course enrollments to new level');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['student_id', 'status']);
            $table->index(['from_level_id', 'to_level_id']);
            $table->index('promotion_code');
            $table->index('academic_year');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_promotions');
    }
};
