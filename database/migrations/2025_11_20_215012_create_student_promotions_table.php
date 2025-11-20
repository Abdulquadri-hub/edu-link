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
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('from_academic_level_id')->constrained('academic_levels')->onDelete('cascade');
            $table->foreignId('to_academic_level_id')->constrained('academic_levels')->onDelete('cascade');
            $table->unsignedBigInteger('promoted_by_id');
            $table->string('promoted_by_type');
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'from_academic_level_id', 'to_academic_level_id'], 'st_prom_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_promotions');
    }
};
