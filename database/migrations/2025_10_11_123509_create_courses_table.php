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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_code')->unique()->index();
            $table->string('title');
            $table->text("description")->nullable();
            $table->enum('category', [
                'academic',
                'programming',
                'data-analyts',
                'tax-audit',
                'business',
                'counseling',
                'other'
            ])->default('academic')->index();
            $table->enum('level', ['beginner', 'intermidiate', 'advanced'])->default('beginner');
            $table->integer('duration_weeks')->default('beginner')->default(12);
            $table->integer('credit_hours')->default(3);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('thumbnail')->nullable();
            $table->text('learning_objectives')->nullable();
            $table->text('prerequisites')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft')->index();
            $table->integer('max_students')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
