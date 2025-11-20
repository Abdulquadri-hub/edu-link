<?php

namespace App\Filament\Parent\Resources\ParentAssignments\Schemas;

use App\Models\Student;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;

class ParentAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        $parent = Auth::user()->parent;

        return $schema
            ->components([
                Section::make('Assignment Details')
                    ->schema([
                        Select::make('student_id')
                            ->label('Select Child')
                            ->options(function () use ($parent) {
                                return $parent->children()
                                    ->where('enrollment_status', 'active')
                                    ->get()
                                    ->pluck('user.full_name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('assignment_id', null))
                            ->helperText('Select which child this assignment is for'),
                        
                        Select::make('assignment_id')
                            ->label('Select Assignment')
                            ->options(function (callable $get) {
                                $studentId = $get('student_id');
                                
                                if (!$studentId) {
                                    return [];
                                }
                                
                                $student = Student::find($studentId);
                                
                                return $student->courses()
                                    ->where('enrollments.status', 'active')
                                    ->with('assignments')
                                    ->get()
                                    ->flatMap(function ($course) use ($studentId) {
                                        return $course->assignments()
                                            ->where('status', 'published')
                                            ->whereDoesntHave('submissions', function ($query) use ($studentId) {
                                                $query->where('student_id', $studentId);
                                            })
                                            ->get()
                                            ->mapWithKeys(function ($assignment) use ($course) {
                                                $dueStatus = $assignment->due_at->isPast() ? ' [OVERDUE]' : ' [Due: ' . $assignment->due_at->format('M d') . ']';
                                                return [
                                                    $assignment->id => $course->course_code . ' - ' . $assignment->title . $dueStatus
                                                ];
                                            });
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->helperText('Only assignments that haven\'t been submitted yet')
                            ->disabled(fn (callable $get) => !$get('student_id')),
                        
                        Placeholder::make('assignment_info')
                            ->label('Assignment Information')
                            ->content(function (callable $get) {
                                $assignmentId = $get('assignment_id');
                                
                                if (!$assignmentId) {
                                    return 'Select an assignment to view details';
                                }
                                
                                $assignment = \App\Models\Assignment::with('course')->find($assignmentId);
                                
                                if (!$assignment) {
                                    return 'Assignment not found';
                                }
                                
                                $dueDate = $assignment->due_at->format('M d, Y H:i');
                                $isOverdue = $assignment->due_at->isPast();
                                $status = $isOverdue ? '⚠️ OVERDUE' : '✓ On Time';
                                
                                return "Course: {$assignment->course->title}\n" .
                                       "Type: {$assignment->type}\n" .
                                       "Max Score: {$assignment->max_score} points\n" .
                                       "Due: {$dueDate} {$status}\n" .
                                       "Description: {$assignment->description}";
                            })
                            ->columnSpanFull()
                            ->visible(fn (callable $get) => $get('assignment_id')),
                    ])
                    ->columns(2),
                
                Section::make('Upload Files')
                    ->schema([
                        FileUpload::make('attachments')
                            ->label('Assignment Files')
                            ->multiple()
                            ->directory('parent-assignments')
                            ->maxFiles(5)
                            ->maxSize(10240) // 10MB
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->helperText('Upload up to 5 files (max 10MB each). Accepted: PDF, Images, Word documents')
                            ->required()
                            ->columnSpanFull(),
                        
                        Textarea::make('parent_notes')
                            ->label('Notes for Instructor')
                            ->rows(4)
                            ->placeholder('Add any notes or context about this assignment...')
                            ->helperText('Optional: Provide additional information to help the instructor')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}