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
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('price_3x_weekly', 10, 2)->nullable()->after('price')->comment('Price for 3 sessions per week');
            $table->decimal('price_5x_weekly', 10, 2)->nullable()->after('price_3x_weekly')->comment('Price for 5 sessions per week');
            $table->integer('subscription_duration_weeks')->default(4)->after('price_5x_weekly')->comment('Default subscription duration in weeks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['price_3x_weekly', 'price_5x_weekly', 'subscription_duration_weeks']);
        });
    }
};
