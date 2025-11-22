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
        Schema::create('parent_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('enrollment_request_id')->nullable()->constrained('enrollment_requests')->onDelete('cascade');
            $table->string('registration_code')->unique();
            $table->string('parent_first_name');
            $table->string('parent_last_name');
            $table->string('parent_email')->unique();
            $table->string('parent_phone')->nullable();
            $table->string('relationship')->comment('father, mother, guardian, etc.');
            $table->string('temporary_password'); // Encrypted
            $table->enum('status', [
                'pending',       // Email sent, awaiting parent action
                'completed',     // Parent logged in and updated password
                'expired'        // Registration link expired
            ])->default('pending');
            $table->foreignId('created_parent_id')->nullable()->constrained('parents')->onDelete('set null');
            $table->foreignId('created_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable()->comment('Registration link expiry');
            $table->timestamps();

            // Indexes
            $table->index(['student_id', 'status']);
            $table->index('registration_code');
            $table->index('parent_email');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_registrations');
    }
};
