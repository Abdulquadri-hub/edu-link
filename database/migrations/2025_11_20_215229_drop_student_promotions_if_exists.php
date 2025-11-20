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
        if (Schema::hasTable('student_promotions')) {
            Schema::dropIfExists('student_promotions');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // noop
    }
};
