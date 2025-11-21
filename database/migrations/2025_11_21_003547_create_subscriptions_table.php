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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->string('subscription_code')->unique(); // Generated code
            $table->enum('frequency', ['3x_weekly', '5x_weekly'])->comment('Number of sessions per week');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'expired', 'cancelled', 'suspended'])->default('active');
            $table->integer('total_sessions')->default(0)->comment('Total sessions included');
            $table->integer('sessions_attended')->default(0)->comment('Sessions attended so far');
            $table->integer('sessions_remaining')->default(0)->comment('Sessions left');
            $table->text('notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['student_id', 'status']);
            $table->index(['course_id', 'status']);
            $table->index('subscription_code');
            $table->index(['start_date', 'end_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
