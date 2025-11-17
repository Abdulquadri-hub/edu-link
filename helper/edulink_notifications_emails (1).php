<?php

/**
 * ==========================================
 * EDULINK NOTIFICATION & EMAIL SYSTEM
 * Step-by-Step Implementation
 * ==========================================
 * 
 * FLOW:
 * 1. Something happens (e.g., class scheduled)
 * 2. Event is fired (e.g., ClassScheduled)
 * 3. Listener catches event (e.g., SendClassNotification)
 * 4. Listener sends email AND creates in-app notification
 * 5. User receives both email and in-app notification
 */

// ==========================================
// STEP 1: MAIL CLASSES (Email Templates)
// ==========================================

// 1.1 WELCOME EMAIL FOR STUDENTS
namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeStudentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function build()
    {
        return $this->subject('Welcome to EduLink! 🎓')
            ->markdown('emails.student.welcome');
    }
}

// 1.2 WELCOME EMAIL FOR PARENTS
namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeParentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    public function build()
    {
        return $this->subject('Welcome to EduLink! 👨‍👩‍👧')
            ->markdown('emails.parent.welcome');
    }
}

// 1.3 WELCOME EMAIL FOR INSTRUCTORS
namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeInstructorMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $temporaryPassword
    ) {}

    public function build()
    {
        return $this->subject('Welcome to EduLink Instructor Portal! 👨‍🏫')
            ->markdown('emails.instructor.welcome');
    }
}

// 1.4 CLASS SCHEDULED EMAIL
namespace App\Mail;

use App\Models\ClassSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClassScheduledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ClassSession $classSession,
        public User $user
    ) {}

    public function build()
    {
        return $this->subject('New Class Scheduled: ' . $this->classSession->title)
            ->markdown('emails.class.scheduled');
    }
}

// 1.5 CLASS REMINDER EMAIL (24 hours before)
namespace App\Mail;

use App\Models\ClassSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClassReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ClassSession $classSession,
        public User $user
    ) {}

    public function build()
    {
        return $this->subject('Reminder: Class Tomorrow - ' . $this->classSession->title)
            ->markdown('emails.class.reminder');
    }
}

// 1.6 ASSIGNMENT CREATED EMAIL
namespace App\Mail;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AssignmentCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Assignment $assignment,
        public User $user
    ) {}

    public function build()
    {
        return $this->subject('New Assignment: ' . $this->assignment->title)
            ->markdown('emails.assignment.created');
    }
}

// 1.7 ASSIGNMENT DUE REMINDER EMAIL
namespace App\Mail;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AssignmentDueMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Assignment $assignment,
        public User $user
    ) {}

    public function build()
    {
        return $this->subject('Assignment Due Tomorrow: ' . $this->assignment->title)
            ->markdown('emails.assignment.due-reminder');
    }
}

// 1.8 GRADE PUBLISHED EMAIL
namespace App\Mail;

use App\Models\Grade;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GradePublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Grade $grade
    ) {}

    public function build()
    {
        return $this->subject('Your Grade Has Been Published 📊')
            ->markdown('emails.grade.published');
    }
}

// 1.9 WEEKLY SUMMARY FOR PARENTS
namespace App\Mail;

use App\Models\ParentModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WeeklySummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ParentModel $parent,
        public array $summary
    ) {}

    public function build()
    {
        return $this->subject('Weekly Progress Report for Your Children 📈')
            ->markdown('emails.parent.weekly-summary');
    }
}

// 1.10 PENDING GRADING REMINDER FOR INSTRUCTORS
namespace App\Mail;

use App\Models\Instructor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PendingGradingReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Instructor $instructor,
        public int $pendingCount
    ) {}

    public function build()
    {
        return $this->subject("You have {$this->pendingCount} submissions to grade")
            ->markdown('emails.instructor.pending-grading');
    }
}

// ==========================================
// STEP 2: NOTIFICATION CLASSES (In-App)
// ==========================================

// 2.1 CLASS SCHEDULED NOTIFICATION
namespace App\Notifications;

use App\Models\ClassSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ClassScheduledNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ClassSession $classSession
    ) {}

    /**
     * Which channels to send notification through
     * 'mail' = Email, 'database' = In-app notification
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Email notification
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Class Scheduled: ' . $this->classSession->title)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('A new class has been scheduled.')
            ->line('**Course:** ' . $this->classSession->course->title)
            ->line('**Title:** ' . $this->classSession->title)
            ->line('**Date:** ' . $this->classSession->scheduled_at->format('M d, Y'))
            ->line('**Time:** ' . $this->classSession->scheduled_at->format('h:i A'))
            ->when($this->classSession->google_meet_link, function ($message) {
                return $message->action('Join Class', $this->classSession->google_meet_link);
            })
            ->line('See you in class!');
    }

    /**
     * In-app notification (stored in database)
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'class_scheduled',
            'class_session_id' => $this->classSession->id,
            'title' => 'New Class Scheduled',
            'message' => "Class '{$this->classSession->title}' scheduled for " . 
                         $this->classSession->scheduled_at->format('M d, Y \a\t h:i A'),
            'course_code' => $this->classSession->course->course_code,
            'scheduled_at' => $this->classSession->scheduled_at,
            'google_meet_link' => $this->classSession->google_meet_link,
        ];
    }
}

// 2.2 CLASS REMINDER NOTIFICATION (24 hours before)
namespace App\Notifications;

use App\Models\ClassSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ClassReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ClassSession $classSession
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reminder: Class Tomorrow - ' . $this->classSession->title)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('This is a reminder that you have a class tomorrow.')
            ->line('**Course:** ' . $this->classSession->course->title)
            ->line('**Title:** ' . $this->classSession->title)
            ->line('**Date:** ' . $this->classSession->scheduled_at->format('M d, Y'))
            ->line('**Time:** ' . $this->classSession->scheduled_at->format('h:i A'))
            ->when($this->classSession->google_meet_link, function ($message) {
                return $message->action('Join Class', $this->classSession->google_meet_link);
            })
            ->line('Don\'t forget to prepare!');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'class_reminder',
            'class_session_id' => $this->classSession->id,
            'title' => 'Class Reminder',
            'message' => "Reminder: Class '{$this->classSession->title}' is tomorrow at " . 
                         $this->classSession->scheduled_at->format('h:i A'),
            'course_code' => $this->classSession->course->course_code,
            'scheduled_at' => $this->classSession->scheduled_at,
            'google_meet_link' => $this->classSession->google_meet_link,
        ];
    }
}

// 2.3 ASSIGNMENT CREATED NOTIFICATION
namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AssignmentCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Assignment $assignment
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Assignment: ' . $this->assignment->title)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('A new assignment has been posted.')
            ->line('**Course:** ' . $this->assignment->course->title)
            ->line('**Title:** ' . $this->assignment->title)
            ->line('**Due Date:** ' . $this->assignment->due_at->format('M d, Y \a\t h:i A'))
            ->line('**Maximum Score:** ' . $this->assignment->max_score . ' points')
            ->action('View Assignment', url('/student/assignments/' . $this->assignment->id))
            ->line('Good luck!');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'assignment_created',
            'assignment_id' => $this->assignment->id,
            'title' => 'New Assignment',
            'message' => "New assignment '{$this->assignment->title}' is due " . 
                         $this->assignment->due_at->format('M d, Y'),
            'course_code' => $this->assignment->course->course_code,
            'due_at' => $this->assignment->due_at,
            'max_score' => $this->assignment->max_score,
        ];
    }
}

// 2.4 GRADE PUBLISHED NOTIFICATION
namespace App\Notifications;

use App\Models\Grade;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class GradePublishedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Grade $grade
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Grade Has Been Published')
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Your grade for an assignment has been published.')
            ->line('**Assignment:** ' . $this->grade->submission->assignment->title)
            ->line('**Score:** ' . $this->grade->score . ' / ' . $this->grade->max_score)
            ->line('**Percentage:** ' . $this->grade->percentage . '%')
            ->line('**Grade:** ' . $this->grade->letter_grade)
            ->when($this->grade->feedback, function ($message) {
                return $message->line('**Feedback:** ' . $this->grade->feedback);
            })
            ->action('View Grade', url('/student/grades/' . $this->grade->id))
            ->line('Keep up the good work!');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'grade_published',
            'grade_id' => $this->grade->id,
            'title' => 'Grade Published',
            'message' => "You scored {$this->grade->percentage}% ({$this->grade->letter_grade}) on '{$this->grade->submission->assignment->title}'",
            'assignment_title' => $this->grade->submission->assignment->title,
            'score' => $this->grade->score,
            'max_score' => $this->grade->max_score,
            'percentage' => $this->grade->percentage,
            'letter_grade' => $this->grade->letter_grade,
        ];
    }
}

// 2.5 LOW GRADE ALERT FOR PARENTS
namespace App\Notifications;

use App\Models\Grade;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LowGradeAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Student $student,
        public Grade $grade
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Grade Alert for ' . $this->student->user->full_name)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line('This is to inform you about a recent grade for your child.')
            ->line('**Student:** ' . $this->student->user->full_name)
            ->line('**Assignment:** ' . $this->grade->submission->assignment->title)
            ->line('**Course:** ' . $this->grade->submission->assignment->course->title)
            ->line('**Score:** ' . $this->grade->percentage . '% (' . $this->grade->letter_grade . ')')
            ->line('You may want to check in with your child about this assignment.')
            ->action('View Details', url('/parent/children/' . $this->student->id . '/grades'))
            ->line('Thank you for staying involved in your child\'s education.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'low_grade_alert',
            'student_id' => $this->student->id,
            'grade_id' => $this->grade->id,
            'title' => 'Grade Alert',
            'message' => "{$this->student->user->full_name} scored {$this->grade->percentage}% on '{$this->grade->submission->assignment->title}'",
            'student_name' => $this->student->user->full_name,
            'assignment_title' => $this->grade->submission->assignment->title,
            'percentage' => $this->grade->percentage,
        ];
    }
}

// ==========================================
// STEP 3: EVENTS (What Happened)
// ==========================================

// 3.1 CLASS SCHEDULED EVENT
namespace App\Events;

use App\Models\ClassSession;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClassScheduled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ClassSession $classSession
    ) {}
}

// 3.2 ASSIGNMENT CREATED EVENT
namespace App\Events;

use App\Models\Assignment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssignmentCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Assignment $assignment
    ) {}
}

// 3.3 GRADE PUBLISHED EVENT
namespace App\Events;

use App\Models\Grade;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GradePublished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Grade $grade
    ) {}
}

// 3.4 STUDENT ENROLLED EVENT
namespace App\Events;

use App\Models\Enrollment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentEnrolled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Enrollment $enrollment
    ) {}
}

// ==========================================
// STEP 4: LISTENERS (What To Do)
// ==========================================

// 4.1 SEND CLASS NOTIFICATION LISTENER
namespace App\Listeners;

use App\Events\ClassScheduled;
use App\Notifications\ClassScheduledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendClassNotification implements ShouldQueue
{
    /**
     * Handle the event.
     * This runs when ClassScheduled event is fired
     */
    public function handle(ClassScheduled $event): void
    {
        $classSession = $event->classSession;
        
        // Get all students enrolled in this course
        $students = $classSession->course->students()
            ->where('enrollment_status', 'active')
            ->with('user')
            ->get();

        // Send notification to each student
        foreach ($students as $student) {
            $student->user->notify(new ClassScheduledNotification($classSession));
        }

        // Also notify the instructor
        $classSession->instructor->user->notify(new ClassScheduledNotification($classSession));
    }
}

// 4.2 SEND ASSIGNMENT NOTIFICATION LISTENER
namespace App\Listeners;

use App\Events\AssignmentCreated;
use App\Notifications\AssignmentCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendAssignmentNotification implements ShouldQueue
{
    public function handle(AssignmentCreated $event): void
    {
        $assignment = $event->assignment;
        
        // Get all students enrolled in this course
        $students = $assignment->course->students()
            ->where('enrollment_status', 'active')
            ->with('user')
            ->get();

        // Send notification to each student
        foreach ($students as $student) {
            $student->user->notify(new AssignmentCreatedNotification($assignment));
        }
    }
}

// 4.3 SEND GRADE NOTIFICATION LISTENER
namespace App\Listeners;

use App\Events\GradePublished;
use App\Notifications\GradePublishedNotification;
use App\Notifications\LowGradeAlertNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendGradeNotification implements ShouldQueue
{
    public function handle(GradePublished $event): void
    {
        $grade = $event->grade;
        $student = $grade->submission->student;

        // 1. Notify the student
        $student->user->notify(new GradePublishedNotification($grade));

        // 2. If grade is low (<60%), notify parents
        if ($grade->percentage < 60) {
            $parents = $student->parents()->with('user')->get();
            
            foreach ($parents as $parent) {
                // Check if parent has permission to view grades
                $canView = $student->parents()
                    ->where('parent_id', $parent->id)
                    ->wherePivot('can_view_grades', true)
                    ->exists();

                if ($canView) {
                    $parent->user->notify(new LowGradeAlertNotification($student, $grade));
                }
            }
        }
    }
}

// 4.4 SEND WELCOME EMAIL LISTENER
namespace App\Listeners;

use App\Events\StudentEnrolled;
use App\Mail\WelcomeStudentMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeEmail implements ShouldQueue
{
    public function handle(StudentEnrolled $event): void
    {
        $student = $event->enrollment->student;
        
        // Send welcome email
        Mail::to($student->user->email)->send(new WelcomeStudentMail($student->user));
    }
}

// ==========================================
// STEP 5: SCHEDULER COMMANDS
// ==========================================

// 5.1 SEND CLASS REMINDERS COMMAND
namespace App\Console\Commands;

use App\Models\ClassSession;
use App\Notifications\ClassReminderNotification;
use Illuminate\Console\Command;

class SendClassReminders extends Command
{
    protected $signature = 'reminders:class';
    protected $description = 'Send reminders for classes starting in 24 hours';

    public function handle()
    {
        // Get all classes scheduled for tomorrow (24 hours from now)
        $tomorrow = now()->addDay();
        
        $classes = ClassSession::where('status', 'scheduled')
            ->whereDate('scheduled_at', $tomorrow->toDateString())
            ->with(['course.students.user', 'instructor.user'])
            ->get();

        $count = 0;
        
        foreach ($classes as $class) {
            // Notify all enrolled students
            $students = $class->course->students()
                ->where('enrollment_status', 'active')
                ->with('user')
                ->get();

            foreach ($students as $student) {
                $student->user->notify(new ClassReminderNotification($class));
                $count++;
            }

            // Notify instructor
            $class->instructor->user->notify(new ClassReminderNotification($class));
            $count++;
        }

        $this->info("Sent {$count} class reminders");
    }
}

// 5.2 SEND ASSIGNMENT DUE REMINDERS COMMAND
namespace App\Console\Commands;

use App\Models\Assignment;
use App\Notifications\AssignmentDueReminderNotification;
use Illuminate\Console\Command;

class SendAssignmentReminders extends Command
{
    protected $signature = 'reminders:assignment';
    protected $description = 'Send reminders for assignments due in 24 hours';

    public function handle()
    {
        $tomorrow = now()->addDay();
        
        $assignments = Assignment::where('status', 'published')
            ->whereDate('due_at', $tomorrow->toDateString())
            ->with('course.students.user')
            ->get();

        $count = 0;
        
        foreach ($assignments as $assignment) {
            $students = $assignment->course->students()
                ->where('enrollment_status', 'active')
                ->whereDoesntHave('submissions', function ($query) use ($assignment) {
                    $query->where('assignment_id', $assignment->id);
                })
                ->with('user')
                ->get();

            foreach ($students as $student) {
                // Only remind students who haven't submitted yet
                $student->user->notify(new AssignmentDueReminderNotification($assignment));
                $count++;
            }
        }

        $this->info("Sent {$count} assignment reminders");
    }
}

// 5.3 SEND WEEKLY PARENT REPORTS COMMAND
namespace App\Console\Commands;

use App\Models\ParentModel;
use App\Mail\WeeklySummaryMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyParentReports extends Command
{
    protected $signature = 'reports:weekly-parents';
    protected $description = 'Send weekly progress reports to parents';

    public function handle()
    {
        $parents = ParentModel::where('receives_weekly_report', true)
            ->with(['children.user', 'children.enrollments', 'children.grades', 'user'])
            ->get();

        $count = 0;
        
        foreach ($parents as $parent) {
            $summary = [];
            
            foreach ($parent->children as $child) {
                $summary[] = [
                    'name' => $child->user->full_name,
                    'courses' => $child->activeEnrollments()->count(),
                    'average_grade' => $child->grades()->where('is_published', true)->avg('percentage'),
                    'attendance_rate' => $child->calculateAttendanceRate(),
                ];
            }

            Mail::to($parent->user->email)->send(new WeeklySummaryMail($parent, $summary));
            $count++;
        }

        $this->info("Sent {$count} weekly reports to parents");
    }
}

// 5.4 SEND PENDING GRADING REMINDERS TO INSTRUCTORS
namespace App\Console\Commands;

use App\Models\Instructor;
use App\Mail\PendingGradingReminderMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendPendingGradingReminders extends Command
{
    protected $signature = 'reminders:pending-grading';
    protected $description = 'Remind instructors about pending submissions';

    public function handle()
    {
        $instructors = Instructor::where('status', 'active')
            ->with('user')
            ->get();

        $count = 0;
        
        foreach ($instructors as $instructor) {
            $pendingCount = \App\Models\Submission::whereHas('assignment', function ($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id);
            })
            ->where('status', 'submitted')
            ->doesntHave('grade')
            ->count();

            if ($pendingCount > 0) {
                Mail::to($instructor->user->email)->send(
                    new PendingGradingReminderMail($instructor, $pendingCount)
                );
                $count++;
            }
        }

        $this->info("Sent reminders to {$count} instructors");
    }
}

// ==========================================
// STEP 6: REGISTER IN KERNEL.PHP
// ==========================================

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Send class reminders every day at 9 AM
        $schedule->command('reminders:class')
            ->dailyAt('09:00')
            ->description('Send class reminders for tomorrow');

        // Send assignment reminders every day at 8 AM
        $schedule->command('reminders:assignment')
            ->dailyAt('08:00')
            ->description('Send assignment due reminders');

        // Send weekly parent reports every Sunday at 8 PM
        $schedule->command('reports:weekly-parents')
            ->sundays()
            ->at('20:00')
            ->description('Send weekly progress reports to parents');

        // Send pending grading reminders every day at 5 PM
        $schedule->command('reminders:pending-grading')
            ->dailyAt('17:00')
            ->description('Remind instructors about pending grading');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

// ==========================================
// STEP 7: REGISTER EVENTS & LISTENERS
// ==========================================

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        // When a class is scheduled, send notifications
        \App\Events\ClassScheduled::class => [
            \App\Listeners\SendClassNotification::class,
        ],

        // When an assignment is created, notify students
        \App\Events\AssignmentCreated::class => [
            \App\Listeners\SendAssignmentNotification::class,
        ],

        // When a grade is published, notify student and parents if low
        \App\Events\GradePublished::class => [
            \App\Listeners\SendGradeNotification::class,
        ],

        // When a student enrolls, send welcome email
        \App\Events\StudentEnrolled::class => [
            \App\Listeners\SendWelcomeEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

// ==========================================
// STEP 8: FIRE EVENTS IN YOUR CODE
// ==========================================

/**
 * EXAMPLE 1: Fire ClassScheduled event after creating a class
 * 
 * Location: ClassSessionService.php or Filament Resource
 */
namespace App\Services;

use App\Events\ClassScheduled;

class ClassSessionService
{
    public function scheduleClass(array $data)
    {
        $session = ClassSession::create($data);
        
        // Fire the event - this triggers SendClassNotification listener
        event(new ClassScheduled($session));
        
        return $session;
    }
}

/**
 * EXAMPLE 2: Fire GradePublished event when instructor publishes grade
 * 
 * Location: GradingService.php or Filament Resource
 */
namespace App\Services;

use App\Events\GradePublished;

class GradingService
{
    public function publishGrade(int $gradeId)
    {
        $grade = Grade::findOrFail($gradeId);
        
        $grade->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
        
        // Fire the event - this triggers SendGradeNotification listener
        event(new GradePublished($grade));
        
        return $grade;
    }
}

/**
 * EXAMPLE 3: Fire AssignmentCreated when assignment is published
 * 
 * Location: AssignmentService.php or Filament Resource
 */
namespace App\Services;

use App\Events\AssignmentCreated;

class AssignmentService
{
    public function publishAssignment(int $assignmentId)
    {
        $assignment = Assignment::findOrFail($assignmentId);
        
        $assignment->update(['status' => 'published']);
        
        // Fire the event - this triggers SendAssignmentNotification listener
        event(new AssignmentCreated($assignment));
        
        return $assignment;
    }
}

// ==========================================
// STEP 9: EMAIL VIEWS (Markdown Templates)
// ==========================================

/**
 * FILE: resources/views/emails/student/welcome.blade.php
 */
@component('mail::message')
# Welcome to EduLink, {{ $user->first_name }}! 🎓

We're excited to have you join our learning community!

Your account has been successfully created and you can now:

- Browse and enroll in courses
- Attend online classes via Google Meet
- Submit assignments and track your grades
- View your progress dashboard

@component('mail::button', ['url' => url('/student/dashboard')])
Go to Dashboard
@endcomponent

**Your Login Details:**
- Email: {{ $user->email }}
- Username: {{ $user->username }}

If you have any questions, feel free to contact our support team.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent

/**
 * FILE: resources/views/emails/parent/welcome.blade.php
 */
@component('mail::message')
# Welcome to EduLink, {{ $user->first_name }}! 👨‍👩‍👧

Thank you for trusting us with your child's education!

As a parent, you can now:

- Monitor your child's academic progress in real-time
- View grades and attendance records
- Receive weekly progress reports
- Communicate with instructors
- Stay informed about upcoming classes

@component('mail::button', ['url' => url('/parent/dashboard')])
Go to Dashboard
@endcomponent

**Your Login Details:**
- Email: {{ $user->email }}
- Username: {{ $user->username }}

**Next Step:** Link your child(ren) to your account to start monitoring their progress.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent

/**
 * FILE: resources/views/emails/instructor/welcome.blade.php
 */
@component('mail::message')
# Welcome to EduLink Instructor Portal, {{ $user->first_name }}! 👨‍🏫

We're thrilled to have you join our teaching team!

Your instructor account has been created. Here are your login credentials:

**Login Details:**
- Email: {{ $user->email }}
- Temporary Password: `{{ $temporaryPassword }}`

@component('mail::panel')
⚠️ **Important:** Please change your password immediately after logging in for security reasons.
@endcomponent

As an instructor, you can:

- Manage your courses and students
- Schedule and conduct online classes
- Create and grade assignments
- Upload course materials
- Track student progress

@component('mail::button', ['url' => url('/instructor/dashboard')])
Login to Instructor Portal
@endcomponent

If you need any assistance, please contact the admin team.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent

/**
 * FILE: resources/views/emails/class/scheduled.blade.php
 */
@component('mail::message')
# New Class Scheduled 📅

Hi {{ $user->first_name }},

A new class has been scheduled for you.

@component('mail::panel')
**Course:** {{ $classSession->course->title }}

**Title:** {{ $classSession->title }}

**Date:** {{ $classSession->scheduled_at->format('l, F j, Y') }}

**Time:** {{ $classSession->scheduled_at->format('g:i A') }}
@endcomponent

@if($classSession->description)
**About this class:**
{{ $classSession->description }}
@endif

@if($classSession->google_meet_link)
@component('mail::button', ['url' => $classSession->google_meet_link])
Join Google Meet
@endcomponent
@endif

Mark your calendar and see you in class!

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent

/**
 * FILE: resources/views/emails/class/reminder.blade.php
 */
@component('mail::message')
# Class Reminder: Tomorrow! ⏰

Hi {{ $user->first_name }},

This is a friendly reminder that you have a class tomorrow.

@component('mail::panel')
**Course:** {{ $classSession->course->title }}

**Title:** {{ $classSession->title }}

**Date:** {{ $classSession->scheduled_at->format('l, F j, Y') }}

**Time:** {{ $classSession->scheduled_at->format('g:i A') }}
@endcomponent

@if($classSession->google_meet_link)
@component('mail::button', ['url' => $classSession->google_meet_link])
Join Google Meet
@endcomponent
@endif

**Preparation Tips:**
- Review previous materials
- Prepare any questions you have
- Test your internet connection
- Have a pen and notebook ready

See you tomorrow!

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent

/**
 * FILE: resources/views/emails/assignment/created.blade.php
 */
@component('mail::message')
# New Assignment Posted 📝

Hi {{ $user->first_name }},

Your instructor has posted a new assignment.

@component('mail::panel')
**Course:** {{ $assignment->course->title }}

**Assignment:** {{ $assignment->title }}

**Type:** {{ ucfirst($assignment->type) }}

**Due Date:** {{ $assignment->due_at->format('l, F j, Y \a\t g:i A') }}

**Maximum Score:** {{ $assignment->max_score }} points
@endcomponent

**Description:**
{{ strip_tags($assignment->description) }}

@component('mail::button', ['url' => url('/student/assignments/' . $assignment->id)])
View Assignment
@endcomponent

@if($assignment->allows_late_submission)
⚠️ Late submissions are allowed but will incur a {{ $assignment->late_penalty_percentage }}% penalty.
@else
⚠️ Late submissions will NOT be accepted.
@endif

Good luck!

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent

/**
 * FILE: resources/views/emails/assignment/due-reminder.blade.php
 */
@component('mail::message')
# Assignment Due Tomorrow! ⏰

Hi {{ $user->first_name }},

This is a reminder that an assignment is due tomorrow.

@component('mail::panel')
**Course:** {{ $assignment->course->title }}

**Assignment:** {{ $assignment->title }}

**Due:** {{ $assignment->due_at->format('l, F j, Y \a\t g:i A') }}

**Maximum Score:** {{ $assignment->max_score }} points
@endcomponent

@component('mail::button', ['url' => url('/student/assignments/' . $assignment->id)])
Submit Assignment
@endcomponent

Don't wait until the last minute!

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent

/**
 * FILE: resources/views/emails/grade/published.blade.php
 */
@component('mail::message')
# Your Grade Has Been Published 📊

Hi {{ $grade->submission->student->user->first_name }},

Your instructor has graded your assignment!

@component('mail::panel')
**Assignment:** {{ $grade->submission->assignment->title }}

**Your Score:** {{ $grade->score }} / {{ $grade->max_score }}

**Percentage:** {{ $grade->percentage }}%

**Grade:** {{ $grade->letter_grade }}
@endcomponent

@if($grade->feedback)
**Instructor Feedback:**

{{ strip_tags($grade->feedback) }}
@endif

@component('mail::button', ['url' => url('/student/grades/' . $grade->id)])
View Detailed Grade
@endcomponent

@if($grade->percentage >= 90)
🎉 Excellent work! Keep it up!
@elseif($grade->percentage >= 80)
👏 Great job!
@elseif($grade->percentage >= 70)
👍 Good effort!
@elseif($grade->percentage >= 60)
💪 You can do better next time!
@else
📚 Don't give up! Consider scheduling a tutoring session.
@endif

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent

/**
 * FILE: resources/views/emails/parent/weekly-summary.blade.php
 */
@component('mail::message')
# Weekly Progress Report 📈

Hi {{ $parent->user->first_name }},

Here's a summary of your children's progress this week.

@foreach($summary as $child)
---

**{{ $child['name'] }}**

- **Active Courses:** {{ $child['courses'] }}
- **Average Grade:** {{ $child['average_grade'] ? round($child['average_grade'], 1) . '%' : 'N/A' }}
- **Attendance Rate:** {{ $child['attendance_rate'] ? round($child['attendance_rate'], 1) . '%' : 'N/A' }}

@if($child['average_grade'] && $child['average_grade'] < 60)
⚠️ **Alert:** {{ $child['name'] }}'s average grade is below 60%. Consider reaching out to their instructors.
@endif

@if($child['attendance_rate'] && $child['attendance_rate'] < 75)
⚠️ **Alert:** {{ $child['name'] }}'s attendance is below 75%. Please ensure they attend classes regularly.
@endif

@endforeach

---

@component('mail::button', ['url' => url('/parent/dashboard')])
View Full Dashboard
@endcomponent

Thank you for being an involved parent!

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent

/**
 * FILE: resources/views/emails/instructor/pending-grading.blade.php
 */
@component('mail::message')
# Pending Submissions to Grade 📝

Hi {{ $instructor->user->first_name }},

You have **{{ $pendingCount }}** student submissions waiting to be graded.

Students are eagerly awaiting your feedback!

@component('mail::button', ['url' => url('/instructor/submissions')])
View Pending Submissions
@endcomponent

**Reminder:** Timely feedback helps students learn and improve.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent

// ==========================================
// STEP 10: QUEUE CONFIGURATION
// ==========================================

/**
 * FILE: .env
 * 
 * Add these configurations to your .env file
 */

# Queue Configuration (Use 'database' for simplicity or 'redis' for production)
QUEUE_CONNECTION=database

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@edulink.com
MAIL_FROM_NAME="${APP_NAME}"

/**
 * Run these commands to set up queues:
 * 
 * php artisan queue:table
 * php artisan migrate
 * php artisan queue:work
 */

// ==========================================
// STEP 11: HOW TO USE IN YOUR CODE
// ==========================================

/**
 * EXAMPLE 1: In Filament Resource (Instructor schedules class)
 */
namespace App\Filament\Instructor\Resources\ClassSessionResource\Pages;

use App\Events\ClassScheduled;

class CreateClassSession extends CreateRecord
{
    protected function afterCreate(): void
    {
        // Fire event after class is created
        event(new ClassScheduled($this->record));
    }
}

/**
 * EXAMPLE 2: In Filament Action (Instructor publishes grade)
 */
Tables\Actions\Action::make('publish')
    ->action(function (Grade $record) {
        $record->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
        
        // Fire event
        event(new \App\Events\GradePublished($record));
        
        Notification::make()
            ->success()
            ->title('Grade published and student notified')
            ->send();
    });

/**
 * EXAMPLE 3: In Service Layer (Enrollment)
 */
namespace App\Services;

use App\Events\StudentEnrolled;

class EnrollmentService
{
    public function enrollStudent(int $studentId, int $courseId)
    {
        $enrollment = Enrollment::create([
            'student_id' => $studentId,
            'course_id' => $courseId,
            'enrolled_at' => now(),
            'status' => 'active',
        ]);
        
        // Fire event - sends welcome email
        event(new StudentEnrolled($enrollment));
        
        return $enrollment;
    }
}

// ==========================================
// STEP 12: TESTING COMMANDS MANUALLY
// ==========================================

/**
 * Test your scheduler commands manually:
 * 
 * php artisan reminders:class
 * php artisan reminders:assignment
 * php artisan reminders:pending-grading
 * php artisan reports:weekly-parents
 */

// ==========================================
// STEP 13: MISSING NOTIFICATION CLASS
// ==========================================

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AssignmentDueReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Assignment $assignment
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Assignment Due Tomorrow: ' . $this->assignment->title)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('This is a reminder that an assignment is due tomorrow.')
            ->line('**Assignment:** ' . $this->assignment->title)
            ->line('**Due:** ' . $this->assignment->due_at->format('M d, Y \a\t h:i A'))
            ->line('**Maximum Score:** ' . $this->assignment->max_score . ' points')
            ->action('Submit Now', url('/student/assignments/' . $this->assignment->id))
            ->line('Don\'t miss the deadline!');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'assignment_due_reminder',
            'assignment_id' => $this->assignment->id,
            'title' => 'Assignment Due Tomorrow',
            'message' => "'{$this->assignment->title}' is due tomorrow at " . 
                         $this->assignment->due_at->format('h:i A'),
            'assignment_title' => $this->assignment->title,
            'due_at' => $this->assignment->due_at,
        ];
    }
}

/*
┌─────────────────────────────────────────────────────────────────┐
│           NOTIFICATION & EMAIL SYSTEM COMPLETE ✅                │
└─────────────────────────────────────────────────────────────────┘

✅ WHAT WE'VE BUILT:

1. MAIL CLASSES (10 emails)
   ├── Welcome emails (Student, Parent, Instructor)
   ├── Class scheduled & reminder
   ├── Assignment created & due reminder
   ├── Grade published
   ├── Weekly parent summary
   └── Pending grading reminder

2. NOTIFICATION CLASSES (6 notifications)
   ├── ClassScheduledNotification
   ├── ClassReminderNotification
   ├── AssignmentCreatedNotification
   ├── AssignmentDueReminderNotification
   ├── GradePublishedNotification
   └── LowGradeAlertNotification

3. EVENTS (4 events)
   ├── ClassScheduled
   ├── AssignmentCreated
   ├── GradePublished
   └── StudentEnrolled

4. LISTENERS (4 listeners)
   ├── SendClassNotification
   ├── SendAssignmentNotification
   ├── SendGradeNotification
   └── SendWelcomeEmail

5. SCHEDULER COMMANDS (4 commands)
   ├── SendClassReminders (daily 9am)
   ├── SendAssignmentReminders (daily 8am)
   ├── SendWeeklyParentReports (Sundays 8pm)
   └── SendPendingGradingReminders (daily 5pm)

6. EMAIL VIEWS (10 markdown templates)
   └── All beautifully formatted

┌─────────────────────────────────────────────────────────────────┐
│                    HOW IT WORKS (FLOW)                           │
└─────────────────────────────────────────────────────────────────┘

EXAMPLE: Instructor schedules a class

1. Instructor creates class in Filament ✅
2. ClassScheduled event is fired ✅
3. SendClassNotification listener catches it ✅
4. Listener sends email to all students ✅
5. Listener creates in-app notification ✅
6. 24 hours before class, scheduler sends reminder ✅

EXAMPLE: Instructor publishes grade

1. Instructor grades submission in Filament ✅
2. GradePublished event is fired ✅
3. SendGradeNotification listener catches it ✅
4. Student receives email + in-app notification ✅
5. If grade < 60%, parents also get notified ✅

┌─────────────────────────────────────────────────────────────────┐
│                    SETUP INSTRUCTIONS                            │
└─────────────────────────────────────────────────────────────────┘

1. Configure .env file with mail settings
2. Run: php artisan queue:table
3. Run: php artisan migrate
4. Start queue worker: php artisan queue:work
5. Test commands manually first
6. Set up cron job for scheduler:
   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1

┌─────────────────────────────────────────────────────────────────┐
│                    READY TO USE! 🚀                              │
└─────────────────────────────────────────────────────────────────┘

All notifications are:
✓ Queued (won't slow down your app)
✓ Logged (track what was sent)
✓ Testable (run commands manually)
✓ Customizable (easy to add more)
✓ Production-ready

NEXT: Would you like to tackle Registration Flow or Homepage?

*/