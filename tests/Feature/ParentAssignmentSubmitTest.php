<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\ParentModel;
use App\Models\ParentAssignment;
use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentAssignmentSubmitTest extends TestCase
{
    use RefreshDatabase;

    protected ParentModel $parent;
    protected Student $student;
    protected Course $course;
    protected Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create parent with a student child
        $parentUser = User::factory()->create(['role' => 'parent']);
        $this->parent = ParentModel::factory()->create(['user_id' => $parentUser->id]);

        $studentUser = User::factory()->create(['role' => 'student']);
        $this->student = Student::factory()->create(['user_id' => $studentUser->id]);

        // Link parent to student
        $this->parent->children()->attach($this->student->id);

        // Create a course and assignment
        $this->course = Course::factory()->create();
        $this->assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'due_at' => now()->addDay(),
        ]);
    }

    public function test_parent_can_upload_for_assignment_submission()
    {
        $data = [
            'student_id' => $this->student->id,
            'assignment_id' => $this->assignment->id,
            'course_id' => null,
            'attachments' => ['path/to/work.pdf'],
            'parent_notes' => 'My child\'s assignment',
            'status' => 'pending',
        ];

        $upload = ParentAssignment::create([
            'parent_id' => $this->parent->id,
            ...$data,
        ]);

        $this->assertDatabaseHas('parent_assignments', [
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'assignment_id' => $this->assignment->id,
            'course_id' => null,
            'status' => 'pending',
        ]);

        $this->assertTrue($upload->assignment_id === $this->assignment->id);
        $this->assertTrue($upload->status === 'pending');
    }

    public function test_submit_to_instructor_creates_submission()
    {
        $upload = ParentAssignment::create([
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'assignment_id' => $this->assignment->id,
            'course_id' => null,
            'status' => 'pending',
            'attachments' => ['file.pdf'],
            'parent_notes' => 'Assignment work',
        ]);

        $upload->submitToInstructor();

        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        $upload->refresh();
        $this->assertEquals('submitted', $upload->status);
        $this->assertNotNull($upload->submission_id);
        $this->assertNotNull($upload->submitted_at);
    }

    public function test_submit_to_instructor_without_assignment_throws_exception()
    {
        $upload = ParentAssignment::create([
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'assignment_id' => null,
            'course_id' => $this->course->id,
            'status' => 'teach',
            'attachments' => ['file.pdf'],
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot submit to instructor without an assignment_id');

        $upload->submitToInstructor();
    }

    public function test_submission_marks_is_late_correctly()
    {
        // Create an overdue assignment
        $overdueAssignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'due_at' => now()->subDay(),
        ]);

        $upload = ParentAssignment::create([
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'assignment_id' => $overdueAssignment->id,
            'course_id' => null,
            'status' => 'pending',
            'attachments' => ['file.pdf'],
        ]);

        $upload->submitToInstructor();

        $submission = Submission::find($upload->submission_id);
        $this->assertTrue($submission->is_late);
    }

    public function test_pending_status_displays_correct_text()
    {
        $upload = ParentAssignment::create([
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'assignment_id' => $this->assignment->id,
            'course_id' => null,
            'status' => 'pending',
            'attachments' => ['file.pdf'],
        ]);

        $this->assertEquals('Not yet submitted', $upload->getSubmissionStatusText());
        $this->assertEquals('warning', $upload->getStatusColorAttribute());
    }

    public function test_submitted_status_displays_correct_text()
    {
        $upload = ParentAssignment::create([
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'assignment_id' => $this->assignment->id,
            'course_id' => null,
            'status' => 'pending',
            'attachments' => ['file.pdf'],
        ]);

        $upload->submitToInstructor();
        $upload->refresh();

        $this->assertEquals('Submitted to instructor', $upload->getSubmissionStatusText());
        $this->assertEquals('info', $upload->getStatusColorAttribute());
    }
}
