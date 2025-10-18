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
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('instructor_id')->index()->unique();
            $table->string("qualification")->nullable();
            $table->text("specialization");
            $table->integer("years_of_experience")->default(0);
            $table->text('bio')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->enum("employment_type", ['full-time', 'part-time', 'contract'])->default('full-time');
            $table->date('hire_date')->default(now());
            $table->enum('status', ['active', 'in-active', 'on-leave'])->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructors');
    }
};
