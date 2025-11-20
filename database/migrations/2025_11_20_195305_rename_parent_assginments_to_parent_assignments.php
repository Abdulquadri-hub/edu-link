<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('parent_assginments')) {
            Schema::rename('parent_assginments', 'parent_assignments');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('parent_assignments')) {
            Schema::rename('parent_assignments', 'parent_assginments');
        }
    }
};
