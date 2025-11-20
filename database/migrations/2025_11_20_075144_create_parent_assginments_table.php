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
        Schema::create('parent_assginments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('parents')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('assignment_id')->nullable()->constrained('assignments')->onDelete('cascade');
            $table->foreignId('submission_id')->nullable()->constrained('submissions')->onDelete('set null');
            $table->text('parent_notes')->nullable();
            $table->json('attachments')->nullable(); // Store file paths
            $table->enum('status', ['pending', 'submitted', 'graded', 'teach'])->default('pending');
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            // $table->softDeletes();


            $table->index(['parent_id', 'student_id']);
            $table->index('assignment_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_assginments');
    }
};
