<?php

namespace App\Filament\Parent\Resources\LinkChildren\Schemas;

use App\Models\Student;
use Filament\Schemas\Schema;
use App\Models\ChildLinkingRequest;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class LinkChildForm
{
    public static function configure(Schema $schema): Schema
    {
        $parent = Auth::user()->parent;
        
        // Get IDs of already linked children
        $linkedChildrenIds = $parent->children()->pluck('students.id')->toArray();
        
        // Get IDs of children with pending requests
        $pendingRequestIds = ChildLinkingRequest::where('parent_id', $parent->id)
            ->where('status', 'pending')
            ->pluck('student_id')
            ->toArray();
        
        // Combine both arrays
        $excludedIds = array_merge($linkedChildrenIds, $pendingRequestIds);

        return $schema
            ->components([
                Section::make('Child Information')
                    ->description(fn (callable $get) => $get('is_new_student') ? 'Enter your child\'s details for registration and linking' : 'Search for your child by their Student ID, name, or email')
                    ->schema([
                        ToggleButtons::make('is_new_student')
                            ->label('Child Status')
                            ->options([
                                false => 'Existing Student',
                                true => 'New Student',
                            ])
                            ->default(false)
                            ->inline()
                            ->reactive()
                            ->columnSpanFull(),
                        
                        // Existing Student Fields
                        Select::make('student_id')
                            ->label('Search Student')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) use ($excludedIds) {
                                return Student::query()
                                    ->whereNotIn('id', $excludedIds)
                                    ->where('enrollment_status', 'active')
                                    ->where(function ($query) use ($search) {
                                        $query->where('student_id', 'like', "%{$search}%")
                                            ->orWhereHas('user', function ($q) use ($search) {
                                                $q->where('first_name', 'like', "%{$search}%")
                                                  ->orWhere('last_name', 'like', "%{$search}%")
                                                  ->orWhere('email', 'like', "%{$search}%");
                                            });
                                    })
                                    ->limit(10)
                                    ->get()
                                    ->mapWithKeys(function ($student) {
                                        return [
                                            $student->id => "{$student->student_id} - {$student->user->full_name} ({$student->user->email})"
                                        ];
                                    });
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $student = Student::find($value);
                                return $student ? "{$student->student_id} - {$student->user->full_name}" : '';
                            })
                            ->required(fn (callable $get) => !$get('is_new_student'))
                            ->reactive()
                            ->helperText('Start typing the student ID, name, or email to search')
                            ->hidden(fn (callable $get) => $get('is_new_student')),
                        
                        TextEntry::make('student_info')
                            ->label('Student Information')
                            ->state(function (callable $get) {
                                $studentId = $get('student_id');
                                
                                if (!$studentId) {
                                    return 'Select a student to view their information';
                                }
                                
                                $student = Student::with(['user', 'academicLevel'])->find($studentId);
                                
                                if (!$student) {
                                    return 'Student not found';
                                }
                                
                                return "Name: {$student->user->full_name}\n" .
                                       "Student ID: {$student->student_id}\n" .
                                       "Email: {$student->user->email}\n" .
                                       "Grade Level: " . ($student->academicLevel?->display_name ?? 'N/A') . "\n" .
                                       "Status: {$student->enrollment_status}";
                            })
                            ->columnSpanFull()
                            ->visible(fn (callable $get) => $get('student_id') && !$get('is_new_student')),

                        // New Student Fields
                        TextInput::make('new_student_first_name')
                            ->label('Child\'s First Name')
                            ->required(fn (callable $get) => $get('is_new_student'))
                            ->maxLength(255)
                            ->hidden(fn (callable $get) => !$get('is_new_student')),
                        
                        TextInput::make('new_student_last_name')
                            ->label('Child\'s Last Name')
                            ->required(fn (callable $get) => $get('is_new_student'))
                            ->maxLength(255)
                            ->hidden(fn (callable $get) => !$get('is_new_student')),
                        
                        TextInput::make('new_student_email')
                            ->label('Child\'s Email (Optional)')
                            ->email()
                            ->maxLength(255)
                            ->hidden(fn (callable $get) => !$get('is_new_student')),
                        
                        DatePicker::make('new_student_dob')
                            ->label('Child\'s Date of Birth')
                            ->required(fn (callable $get) => $get('is_new_student'))
                            ->maxDate(now())
                            ->hidden(fn (callable $get) => !$get('is_new_student')),
                        
                        TextInput::make('new_student_grade_level')
                            ->label('Child\'s Grade Level (e.g., 5th Grade)')
                            ->required(fn (callable $get) => $get('is_new_student'))
                            ->maxLength(255)
                            ->hidden(fn (callable $get) => !$get('is_new_student')),
                    ])
                    ->columns(1),
                
                Section::make('Relationship Details')
                    ->schema([
                        Select::make('relationship')
                            ->label('Your Relationship to Student')
                            ->options([
                                'father' => 'Father',
                                'mother' => 'Mother',
                                'guardian' => 'Legal Guardian',
                                'grandparent' => 'Grandparent',
                                'sibling' => 'Sibling (Over 18)',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->searchable(),
                        
                        Toggle::make('is_primary_contact')
                            ->label('Set as Primary Contact')
                            ->helperText('Primary contact receives all important notifications')
                            ->default(false),
                        
                        Toggle::make('can_view_grades')
                            ->label('Can View Grades')
                            ->helperText('Allow viewing student grades and academic performance')
                            ->default(true),
                        
                        Toggle::make('can_view_attendance')
                            ->label('Can View Attendance')
                            ->helperText('Allow viewing student attendance records')
                            ->default(true),
                    ])
                    ->columns(2),
                
                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('parent_message')
                            ->label('Message to Admin (Optional)')
                            ->rows(4)
                            ->placeholder('Provide any additional information that might help verify your relationship with this student...')
                            ->helperText('This will be reviewed by an administrator')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
