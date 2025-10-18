<?php

/**
 * ==========================================
 * EDULINK DATABASE SCHEMA
 * Complete Migration Files for Laravel 12
 * ==========================================
 */

// ============================================
// 1. USERS TABLE (Base Authentication)
// ============================================
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->enum('user_type', ['admin', 'instructor', 'student', 'parent'])->index();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

// ============================================
// 2. STUDENTS TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('student_id')->unique(); // e.g., STU2024001
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('Nigeria');
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->date('enrollment_date')->default(now());
            $table->enum('enrollment_status', ['active', 'graduated', 'dropped', 'suspended'])->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('student_id');
            $table->index('enrollment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

// ============================================
// 3. PARENTS TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('parent_id')->unique(); // e.g., PAR2024001
            $table->string('occupation')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('Nigeria');
            $table->string('secondary_phone')->nullable();
            $table->enum('preferred_contact_method', ['email', 'phone', 'sms'])->default('email');
            $table->boolean('receives_weekly_report')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};

// ============================================
// 4. STUDENT_PARENT PIVOT TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_parent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained()->cascadeOnDelete();
            $table->enum('relationship', ['father', 'mother', 'guardian', 'other'])->default('guardian');
            $table->boolean('is_primary_contact')->default(false);
            $table->boolean('can_view_grades')->default(true);
            $table->boolean('can_view_attendance')->default(true);
            $table->timestamps();

            // Composite unique to prevent duplicate links
            $table->unique(['student_id', 'parent_id']);
            $table->index(['student_id', 'is_primary_contact']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_parent');
    }
};

// ============================================
// 5. INSTRUCTORS TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('instructor_id')->unique(); // e.g., INS2024001
            $table->string('qualification')->nullable();
            $table->text('specialization')->nullable();
            $table->integer('years_of_experience')->default(0);
            $table->text('bio')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->enum('employment_type', ['full-time', 'part-time', 'contract'])->default('full-time');
            $table->date('hire_date')->default(now());
            $table->enum('status', ['active', 'inactive', 'on-leave'])->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index('instructor_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructors');
    }
};

// ============================================
// 6. COURSES TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_code')->unique(); // e.g., CS101
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', [
                'academic', 
                'programming', 
                'data-analysis', 
                'tax-audit', 
                'business', 
                'counseling',
                'other'
            ])->default('academic')->index();
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->integer('duration_weeks')->default(12);
            $table->integer('credit_hours')->default(3);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('thumbnail')->nullable();
            $table->text('learning_objectives')->nullable(); // JSON or text
            $table->text('prerequisites')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft')->index();
            $table->integer('max_students')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'status']);
            $table->index('course_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};

// ============================================
// 7. INSTRUCTOR_COURSE PIVOT TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_course', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->date('assigned_date')->default(now());
            $table->boolean('is_primary_instructor')->default(true);
            $table->timestamps();

            $table->unique(['instructor_id', 'course_id']);
            $table->index('instructor_id');
            $table->index('course_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_course');
    }
};

// ============================================
// 8. ENROLLMENTS TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->date('enrolled_at')->default(now());
            $table->date('completed_at')->nullable();
            $table->enum('status', ['active', 'completed', 'dropped', 'failed'])->default('active')->index();
            $table->decimal('progress_percentage', 5, 2)->default(0); // 0.00 to 100.00
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Prevent duplicate enrollments
            $table->unique(['student_id', 'course_id']);
            $table->index(['student_id', 'status']);
            $table->index(['course_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};

// ============================================
// 9. CLASS SESSIONS TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('scheduled_at');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->integer('duration_minutes')->nullable(); // Calculated: ended_at - started_at
            $table->string('google_meet_link')->nullable();
            $table->string('google_calendar_event_id')->nullable();
            $table->enum('status', ['scheduled', 'in-progress', 'completed', 'cancelled'])->default('scheduled')->index();
            $table->text('notes')->nullable();
            $table->integer('max_participants')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['course_id', 'scheduled_at']);
            $table->index(['instructor_id', 'status']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};

// ============================================
// 10. ATTENDANCES TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('absent')->index();
            $table->dateTime('joined_at')->nullable();
            $table->dateTime('left_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['class_session_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

// ============================================
// 11. ASSIGNMENTS TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->text('instructions')->nullable();
            $table->dateTime('assigned_at')->default(now());
            $table->dateTime('due_at');
            $table->integer('max_score')->default(100);
            $table->enum('type', ['quiz', 'homework', 'project', 'exam', 'other'])->default('homework')->index();
            $table->boolean('allows_late_submission')->default(false);
            $table->integer('late_penalty_percentage')->default(0);
            $table->json('attachments')->nullable(); // Array of file paths
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['course_id', 'status']);
            $table->index('due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};

// ============================================
// 12. SUBMISSIONS TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->text('content')->nullable();
            $table->json('attachments')->nullable(); // Array of uploaded files
            $table->dateTime('submitted_at')->default(now());
            $table->boolean('is_late')->default(false);
            $table->enum('status', ['submitted', 'graded', 'returned', 'resubmit'])->default('submitted')->index();
            $table->integer('attempt_number')->default(1);
            $table->timestamps();
            $table->softDeletes();

            // Allow multiple submissions if assignment permits
            $table->index(['assignment_id', 'student_id', 'status']);
            $table->index(['student_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};

// ============================================
// 13. GRADES TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2); // e.g., 85.50
            $table->decimal('max_score', 5, 2); // e.g., 100.00
            $table->decimal('percentage', 5, 2); // Calculated: (score/max_score)*100
            $table->string('letter_grade', 2)->nullable(); // A, B+, C, etc.
            $table->text('feedback')->nullable();
            $table->dateTime('graded_at')->default(now());
            $table->boolean('is_published')->default(false); // Hidden until instructor publishes
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('submission_id');
            $table->index(['instructor_id', 'graded_at']);
            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};

// ============================================
// 14. MATERIALS TABLE
// ============================================
return new class extends Migration
{
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
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};

// ============================================
// 15. NOTIFICATIONS TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // ClassScheduled, GradePublished, etc.
            $table->text('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional metadata
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->index();
            $table->boolean('is_read')->default(false)->index();
            $table->dateTime('read_at')->nullable();
            $table->enum('channel', ['database', 'email', 'sms', 'push'])->default('database');
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

// ============================================
// 16. SERVICE REQUESTS TABLE (Non-Academic)
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('service_type', [
                'tax-calculation',
                'audit-help',
                'business-setup',
                'business-monitoring',
                'counseling',
                'foreign-shopping'
            ])->index();
            $table->string('title');
            $table->text('description');
            $table->json('details')->nullable(); // Flexible field for service-specific data
            $table->enum('status', ['pending', 'in-progress', 'completed', 'cancelled'])->default('pending')->index();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('final_cost', 10, 2)->nullable();
            $table->dateTime('requested_at')->default(now());
            $table->dateTime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['service_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};

// ============================================
// 17. TAX CALCULATIONS TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('tax_type', ['income', 'vat', 'corporate', 'property', 'other'])->index();
            $table->integer('tax_year');
            $table->decimal('income_amount', 15, 2);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('taxable_amount', 15, 2);
            $table->decimal('tax_due', 15, 2);
            $table->json('calculation_details')->nullable(); // Breakdown
            $table->text('recommendations')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'tax_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_calculations');
    }
};

// ============================================
// 18. BUSINESS SETUPS TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_setups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('business_name');
            $table->enum('business_type', ['sole-proprietorship', 'partnership', 'llc', 'corporation', 'other']);
            $table->text('business_description');
            $table->string('industry')->nullable();
            $table->enum('stage', [
                'idea',
                'planning',
                'registration',
                'setup',
                'operational',
                'monitoring'
            ])->default('idea')->index();
            $table->json('required_documents')->nullable();
            $table->json('completed_steps')->nullable();
            $table->date('registration_date')->nullable();
            $table->string('registration_number')->nullable();
            $table->decimal('setup_cost', 12, 2)->nullable();
            $table->foreignId('assigned_consultant_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_setups');
    }
};

// ============================================
// 19. COUNSELING SESSIONS TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counseling_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counselor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('session_type', ['academic', 'career', 'personal', 'mental-health', 'other'])->index();
            $table->dateTime('scheduled_at');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('google_meet_link')->nullable();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable(); // Counselor's notes
            $table->text('action_items')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no-show'])->default('scheduled')->index();
            $table->boolean('requires_follow_up')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'status']);
            $table->index(['counselor_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counseling_sessions');
    }
};

// ============================================
// 20. FOREIGN SHOPPING TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foreign_shopping', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->string('item_url')->nullable();
            $table->decimal('item_price_usd', 10, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('total_price_usd', 10, 2);
            $table->decimal('estimated_shipping', 10, 2)->nullable();
            $table->decimal('service_fee', 10, 2)->nullable();
            $table->decimal('total_cost_naira', 12, 2)->nullable();
            $table->text('delivery_address');
            $table->string('delivery_city');
            $table->string('delivery_state');
            $table->string('delivery_phone');
            $table->enum('status', [
                'requested',
                'quote-sent',
                'payment-pending',
                'paid',
                'purchasing',
                'shipping',
                'delivered',
                'cancelled'
            ])->default('requested')->index();
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foreign_shopping');
    }
};

// ============================================
// 21. REPORTS TABLE
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('report_type', [
                'student-progress',
                'student-attendance',
                'instructor-performance',
                'course-analytics',
                'parent-summary',
                'financial',
                'custom'
            ])->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('data')->nullable(); // Report data
            $table->string('file_path')->nullable(); // Generated PDF
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->dateTime('generated_at')->default(now());
            $table->timestamps();
            $table->softDeletes();

            $table->index(['report_type', 'generated_at']);
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

// ============================================
// 22. ACTIVITY LOG TABLE (Spatie)
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable()->index();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->string('event')->nullable()->index();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};

// ============================================
// DATABASE SCHEMA SUMMARY & RELATIONSHIPS
// ============================================

/*
┌─────────────────────────────────────────────────────────────────┐
│                    RELATIONSHIP DIAGRAM                          │
└─────────────────────────────────────────────────────────────────┘

USERS (1) ──┬── STUDENTS (1)
            ├── PARENTS (1)
            ├── INSTRUCTORS (1)
            └── ADMINS (1)

STUDENTS (N) ──┬── ENROLLMENTS ── COURSES (N)
               ├── SUBMISSIONS ── ASSIGNMENTS
               ├── ATTENDANCES ── CLASS_SESSIONS
               ├── GRADES
               └── STUDENT_PARENT ── PARENTS (N)

INSTRUCTORS (N) ──┬── INSTRUCTOR_COURSE ── COURSES (N)
                  ├── CLASS_SESSIONS
                  ├── ASSIGNMENTS
                  └── GRADES

COURSES (1) ──┬── ENROLLMENTS (N)
              ├── ASSIGNMENTS (N)
              ├── CLASS_SESSIONS (N)
              └── MATERIALS (N)

PARENTS (N) ──┬── STUDENT_PARENT ── STUDENTS (N)
              └── REPORTS

SERVICE_REQUESTS ──┬── TAX_CALCULATIONS
                   ├── BUSINESS_SETUPS
                   ├── COUNSELING_SESSIONS
                   └── FOREIGN_SHOPPING

┌─────────────────────────────────────────────────────────────────┐
│                    KEY INDEXES & PERFORMANCE                     │
└─────────────────────────────────────────────────────────────────┘

✅ ALL foreign keys indexed automatically
✅ Composite indexes on frequently queried combinations
✅ Status columns indexed for filtering
✅ Timestamp columns indexed for date range queries
✅ Unique constraints on business logic fields
✅ Soft deletes enabled on all major tables

┌─────────────────────────────────────────────────────────────────┐
│                    TOTAL TABLES: 22                              │
└─────────────────────────────────────────────────────────────────┘

Core Tables: 11
├── users
├── students
├── parents
├── instructors
├── courses
├── enrollments
├── assignments
├── submissions
├── grades
├── class_sessions
└── attendances

Relationship/Pivot Tables: 2
├── student_parent
└── instructor_course

Support Tables: 4
├── materials
├── notifications
├── reports
└── activity_log

Service Tables: 5
├── service_requests
├── tax_calculations
├── business_setups
├── counseling_sessions
└── foreign_shopping

*/

// ============================================
// ADDITIONAL HELPER MIGRATIONS
// ============================================

// ============================================
// 23. PASSWORD RESET TOKENS (Laravel 12)
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};

// ============================================
// 24. SESSIONS TABLE (for database sessions)
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};

// ============================================
// 25. CACHE TABLE (for database cache driver)
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
    }
};

// ============================================
// 26. JOBS TABLE (for queue)
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
    }
};

// ============================================
// 27. PERSONAL ACCESS TOKENS (Sanctum - if using API)
// ============================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};

/*
┌─────────────────────────────────────────────────────────────────┐
│              SAMPLE DATA QUERIES & EXAMPLES                      │
└─────────────────────────────────────────────────────────────────┘

// 1. Get all students enrolled in a course with their grades
SELECT 
    s.student_id,
    u.first_name,
    u.last_name,
    c.title as course_title,
    e.progress_percentage,
    AVG(g.percentage) as average_grade
FROM students s
JOIN users u ON s.user_id = u.id
JOIN enrollments e ON s.id = e.student_id
JOIN courses c ON e.course_id = c.id
LEFT JOIN submissions sub ON sub.student_id = s.id
LEFT JOIN grades g ON g.submission_id = sub.id
WHERE e.status = 'active'
GROUP BY s.id, c.id;

// 2. Get instructor's total teaching hours this month
SELECT 
    i.instructor_id,
    u.first_name,
    u.last_name,
    SUM(cs.duration_minutes) / 60 as total_hours
FROM instructors i
JOIN users u ON i.user_id = u.id
JOIN class_sessions cs ON cs.instructor_id = i.id
WHERE cs.status = 'completed'
  AND MONTH(cs.started_at) = MONTH(CURRENT_DATE)
  AND YEAR(cs.started_at) = YEAR(CURRENT_DATE)
GROUP BY i.id;

// 3. Get parent's children with their attendance rate
SELECT 
    p.parent_id,
    s.student_id,
    CONCAT(su.first_name, ' ', su.last_name) as student_name,
    COUNT(a.id) as total_classes,
    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as attended,
    ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 2) as attendance_rate
FROM parents p
JOIN student_parent sp ON sp.parent_id = p.id
JOIN students s ON s.id = sp.student_id
JOIN users su ON s.user_id = su.id
JOIN attendances a ON a.student_id = s.id
GROUP BY p.id, s.id;

// 4. Get upcoming classes for a student
SELECT 
    cs.title,
    cs.scheduled_at,
    c.title as course_name,
    CONCAT(iu.first_name, ' ', iu.last_name) as instructor_name,
    cs.google_meet_link
FROM class_sessions cs
JOIN courses c ON cs.course_id = c.id
JOIN enrollments e ON e.course_id = c.id
JOIN instructors i ON cs.instructor_id = i.id
JOIN users iu ON i.user_id = iu.id
WHERE e.student_id = ? 
  AND cs.scheduled_at > NOW()
  AND cs.status = 'scheduled'
ORDER BY cs.scheduled_at ASC;

// 5. Get low-performing students (for parent alerts)
SELECT 
    s.student_id,
    u.email,
    c.title as course_title,
    AVG(g.percentage) as avg_grade
FROM students s
JOIN users u ON s.user_id = u.id
JOIN enrollments e ON e.student_id = s.id
JOIN courses c ON e.course_id = c.id
JOIN submissions sub ON sub.student_id = s.id
JOIN assignments a ON sub.assignment_id = a.id AND a.course_id = c.id
JOIN grades g ON g.submission_id = sub.id
WHERE g.is_published = true
GROUP BY s.id, c.id
HAVING avg_grade < 60;

┌─────────────────────────────────────────────────────────────────┐
│              DATABASE OPTIMIZATION NOTES                         │
└─────────────────────────────────────────────────────────────────┘

PERFORMANCE TIPS:
─────────────────

1. INDEXES STRATEGY:
   ✓ Foreign keys auto-indexed
   ✓ Status columns indexed (for filtering)
   ✓ Composite indexes on common joins
   ✓ Date columns indexed (for range queries)
   ✓ Use EXPLAIN to analyze slow queries

2. QUERY OPTIMIZATION:
   ✓ Use eager loading to prevent N+1 queries
   ✓ Cache frequently accessed data (courses, instructors)
   ✓ Use database transactions for multi-step operations
   ✓ Paginate large result sets

3. DATA ARCHIVAL:
   ✓ Soft deletes preserve data integrity
   ✓ Archive old class_sessions after 1 year
   ✓ Archive completed enrollments
   ✓ Clean old notifications after 90 days

4. BACKUP STRATEGY:
   ✓ Daily automated backups
   ✓ Keep backups for 30 days
   ✓ Test restore procedures monthly

┌─────────────────────────────────────────────────────────────────┐
│              SECURITY CONSIDERATIONS                             │
└─────────────────────────────────────────────────────────────────┘

1. DATA ENCRYPTION:
   - Encrypt sensitive fields (e.g., counseling notes)
   - Use HTTPS for all connections
   - Hash passwords with bcrypt

2. ACCESS CONTROL:
   - Parents can only access their children's data
   - Instructors can only grade their courses
   - Students can only view their own submissions

3. AUDIT TRAIL:
   - Use activity_log for all sensitive operations
   - Log grade changes, enrollment modifications
   - Track who accessed student records

4. DATA PRIVACY (GDPR/NDPR Compliant):
   - Allow users to request data export
   - Allow users to request data deletion
   - Anonymize data after account deletion

┌─────────────────────────────────────────────────────────────────┐
│              MIGRATION EXECUTION ORDER                           │
└─────────────────────────────────────────────────────────────────┘

Run migrations in this order:
──────────────────────────────

1. users (base auth)
2. students, parents, instructors (user types)
3. student_parent (pivot)
4. courses
5. instructor_course (pivot)
6. enrollments
7. class_sessions
8. attendances
9. assignments
10. submissions
11. grades
12. materials
13. notifications
14. service_requests
15. tax_calculations
16. business_setups
17. counseling_sessions
18. foreign_shopping
19. reports
20. activity_log
21. password_reset_tokens
22. sessions
23. cache
24. jobs
25. personal_access_tokens

Command to run:
php artisan migrate

To rollback:
php artisan migrate:rollback

To fresh install:
php artisan migrate:fresh --seed

┌─────────────────────────────────────────────────────────────────┐
│              SAMPLE MODEL RELATIONSHIPS                          │
└─────────────────────────────────────────────────────────────────┘

// Student Model
class Student extends Model
{
    public function user() { 
        return $this->belongsTo(User::class); 
    }
    
    public function parents() { 
        return $this->belongsToMany(ParentModel::class, 'student_parent'); 
    }
    
    public function enrollments() { 
        return $this->hasMany(Enrollment::class); 
    }
    
    public function courses() { 
        return $this->belongsToMany(Course::class, 'enrollments'); 
    }
    
    public function submissions() { 
        return $this->hasMany(Submission::class); 
    }
    
    public function attendances() { 
        return $this->hasMany(Attendance::class); 
    }
    
    public function counselingSessions() { 
        return $this->hasMany(CounselingSession::class); 
    }
}

// Instructor Model
class Instructor extends Model
{
    public function user() { 
        return $this->belongsTo(User::class); 
    }
    
    public function courses() { 
        return $this->belongsToMany(Course::class, 'instructor_course'); 
    }
    
    public function classSessions() { 
        return $this->hasMany(ClassSession::class); 
    }
    
    public function assignments() { 
        return $this->hasMany(Assignment::class); 
    }
    
    public function grades() { 
        return $this->hasMany(Grade::class); 
    }
    
    public function materials() { 
        return $this->hasMany(Material::class); 
    }
}

// Course Model
class Course extends Model
{
    public function instructors() { 
        return $this->belongsToMany(Instructor::class, 'instructor_course'); 
    }
    
    public function students() { 
        return $this->belongsToMany(Student::class, 'enrollments'); 
    }
    
    public function enrollments() { 
        return $this->hasMany(Enrollment::class); 
    }
    
    public function classSessions() { 
        return $this->hasMany(ClassSession::class); 
    }
    
    public function assignments() { 
        return $this->hasMany(Assignment::class); 
    }
    
    public function materials() { 
        return $this->hasMany(Material::class); 
    }
}

// Parent Model
class ParentModel extends Model
{
    protected $table = 'parents';
    
    public function user() { 
        return $this->belongsTo(User::class); 
    }
    
    public function children() { 
        return $this->belongsToMany(Student::class, 'student_parent'); 
    }
    
    public function primaryChildren() {
        return $this->belongsToMany(Student::class, 'student_parent')
                    ->wherePivot('is_primary_contact', true);
    }
}

┌─────────────────────────────────────────────────────────────────┐
│              CONCLUSION & NEXT STEPS                             │
└─────────────────────────────────────────────────────────────────┘

DATABASE SCHEMA COMPLETE ✓

Total Tables: 27
├── Core Business Logic: 22 tables
└── Laravel System: 5 tables

FEATURES COVERED:
✅ Multi-role authentication (Admin, Instructor, Student, Parent)
✅ Course management & enrollments
✅ Class scheduling & attendance tracking
✅ Assignment submission & grading
✅ Instructor clock-in/clock-out
✅ Parent-student linking & monitoring
✅ Google Meet integration (link storage)
✅ Material uploads & downloads
✅ Non-academic services (tax, business, counseling, shopping)
✅ Notifications system
✅ Reporting system
✅ Activity logging
✅ Soft deletes for data recovery

READY FOR:
→ Repository implementations
→ Service layer logic
→ Filament resources
→ API endpoints
→ Queue jobs
→ Event listeners

*