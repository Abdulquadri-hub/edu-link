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
        Schema::create('enrollment_requests', function (Blueprint $table) {
             $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('request_code')->unique(); // Generated code for tracking
            $table->enum('frequency_preference', ['3x_weekly', '5x_weekly'])->comment('Preferred session frequency');
            $table->decimal('quoted_price', 10, 2)->comment('Price at time of request');
            $table->string('currency')->default('USD');
            $table->text('student_message')->nullable()->comment('Why student wants to enroll');
            $table->enum('status', [
                'pending',           // Waiting for parent/admin action
                'parent_notified',   // Parent has been notified
                'payment_pending',   // Waiting for payment
                'approved',          // Enrollment created
                'rejected',          // Request denied
                'cancelled'          // Student cancelled
            ])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['student_id', 'status']);
            $table->index(['course_id', 'status']);
            $table->index('request_code');
            $table->index('status');
            
            // Prevent duplicate pending requests
            $table->unique(['student_id', 'course_id', 'status'], 'unique_pending_enrollment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_requests');
    }
};
