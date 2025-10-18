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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2); // e.g., 85.50
            $table->decimal('max_score', 5, 2); // e.g., 100.00
            $table->decimal('percentage', 5, 2); // Calculated: (score/max_score)*100
            $table->string('letter_grade', 2)->nullable(); // A, B+, C, etc.
            $table->text('feedback')->nullable();
            $table->dateTime('graded_at')->default(now());
            $table->boolean('is_published')->default(false); // Hidden until instructor publishes
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('submission_id');
            $table->index(['instructor_id', 'graded_at']);
            $table->index('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
