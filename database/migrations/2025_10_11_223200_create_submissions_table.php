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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->text('content')->nullable();
            $table->json('attachments')->nullable(); 
            $table->dateTime('submitted_at')->default(now());
            $table->boolean('is_late')->default(false);
            $table->enum('status', ['submitted', 'graded', 'returned', 'resubmit'])->default('submitted')->index();
            $table->integer('attempt_number')->default(1);
            $table->timestamps();
            $table->softDeletes();


            $table->index(['assignment_id', 'student_id', 'status']);
            $table->index(['student_id', 'submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
