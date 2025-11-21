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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('parent_id')->constrained('parents')->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('set null');
            $table->string('payment_reference')->unique(); // Generated reference number
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('USD');
            $table->enum('payment_method', ['bank_transfer', 'credit_card', 'cash', 'mobile_money', 'other'])->default('bank_transfer');
            $table->string('receipt_path')->nullable(); // File storage path
            $table->string('receipt_filename')->nullable();
            $table->text('parent_notes')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('payment_date')->nullable(); // Date parent claims payment was made
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['parent_id', 'status']);
            $table->index(['student_id', 'status']);
            $table->index(['course_id', 'status']);
            $table->index('payment_reference');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
