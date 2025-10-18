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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->text('instructions')->nullable();
            $table->dateTime('assigned_at')->default(now());
            $table->dateTime('due_at');
            $table->integer('max_score')->default(100);
            $table->enum('type', ['quiz', 'homework', 'project', 'exam', 'other'])->default('homework')->index();
            $table->boolean('allows_late_submission')->default(false);
            $table->integer('late_penalty_percentage')->default(0);
            $table->json('attachments')->nullable(); // Array of file paths
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['course_id', 'status']);
            $table->index('due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
