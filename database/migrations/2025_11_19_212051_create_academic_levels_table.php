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
        Schema::create('academic_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Grade 1", "Grade 2"
            $table->integer('grade_number'); // 1, 2, 3, etc.
            $table->text('description')->nullable();
            $table->enum('level_type', ['elementary', 'middle', 'high'])->default('elementary');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('grade_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_levels');
    }
};
