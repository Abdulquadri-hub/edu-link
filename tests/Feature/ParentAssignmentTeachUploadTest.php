<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\ParentModel;
use App\Models\ParentAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentAssignmentTeachUploadTest extends TestCase
{
    use RefreshDatabase;

    protected ParentModel $parent;
    protected Student $student;
    protected Course $course;

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

        // Create a course
        $this->course = Course::factory()->create();
    }

    public function test_parent_can_upload_teaching_material_with_course_id()
    {
        $data = [
            'student_id' => $this->student->id,
            'course_id' => $this->course->id,
            'assignment_id' => null,
            'attachments' => ['path/to/file.pdf'],
            'parent_notes' => 'Material for teaching',
            'status' => 'teach',
        ];

        $upload = ParentAssignment::create([
            'parent_id' => $this->parent->id,
            ...$data,
        ]);

        $this->assertDatabaseHas('parent_assignments', [
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'course_id' => $this->course->id,
            'assignment_id' => null,
            'status' => 'teach',
        ]);

        $this->assertTrue($upload->course_id === $this->course->id);
        $this->assertTrue($upload->status === 'teach');
    }

    public function test_teach_upload_has_course_relation()
    {
        $upload = ParentAssignment::create([
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'course_id' => $this->course->id,
            'assignment_id' => null,
            'status' => 'teach',
            'attachments' => ['file.pdf'],
        ]);

        $this->assertNotNull($upload->course);
        $this->assertEquals($this->course->id, $upload->course->id);
    }

    public function test_teach_status_displays_correct_text()
    {
        $upload = ParentAssignment::create([
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'course_id' => $this->course->id,
            'assignment_id' => null,
            'status' => 'teach',
            'attachments' => ['file.pdf'],
        ]);

        $this->assertEquals('Upload for instructor to teach', $upload->getSubmissionStatusText());
        $this->assertEquals('primary', $upload->getStatusColorAttribute());
    }
}
