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
        Schema::create('materials', function (Blueprint $table) {
             $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['pdf', 'video', 'slide', 'document', 'link', 'other'])->default('document')->index();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable(); // in bytes
            $table->string('external_url')->nullable(); // For YouTube, Drive links, etc.
            $table->integer('download_count')->default(0);
            $table->boolean('is_downloadable')->default(true);
            $table->dateTime('uploaded_at')->default(now());
            $table->enum('status', ['draft', 'published', 'archived'])->default('published')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['course_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
