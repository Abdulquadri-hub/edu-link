<?php

namespace App\Filament\Admin\Resources\StudentPromotions\Schemas;

use App\Models\Student;
use Filament\Schemas\Schema;
use App\Models\AcademicLevel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\TextEntry;

class StudentPromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Student Information')
                    ->schema([
                        Select::make('student_id')
                            ->label('Select Student')
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->getSearchResultsUsing(function (string $search) {
                                return Student::query()
                                    ->where('enrollment_status', 'active')
                                    ->where(function ($query) use ($search) {
                                        $query->where('student_id', 'like', "%{$search}%")
                                            ->orWhereHas('user', function ($q) use ($search) {
                                                $q->where('first_name', 'like', "%{$search}%")
                                                  ->orWhere('last_name', 'like', "%{$search}%");
                                            });
                                    })
                                    ->limit(10)
                                    ->get()
                                    ->mapWithKeys(function ($student) {
                                      return [
                                            $student->id => "{$student->student_id} - {$student->user->full_name} (Current: " . ($student->academicLevel?->name ?? 'Not Set') . ")"
                                        ];
                                    });
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $student = Student::find($value);
                                return $student ? "{$student->student_id} - {$student->user->full_name}" : '';
                            })
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $student = Student::find($state);
                                    $set('from_level_id', $student->academic_level_id);
                                }
                            }),
                        
                        TextEntry::make('current_level_info')
                            ->label('Current Grade Level')
                            ->state(function (callable $get) {
                                $studentId = $get('student_id');
                                if (!$studentId) return 'Select a student to view their current level';
                                
                                $student = Student::with('academicLevel')->find($studentId);
                                if (!$student) return 'Student not found';
                                
                                if (!$student->academicLevel) {
                                    return '⚠️ No grade level assigned';
                                }
                                
                                return "Current Level: {$student->academicLevel->display_name}\n" .
                                       "Grade Number: {$student->academicLevel->grade_number}\n" .
                                       "Type: " . ucfirst($student->academicLevel->level_type);
                            })
                            ->visible(fn (callable $get) => $get('student_id')),
                    ]),
                
                Section::make('Promotion Details')
                    ->schema([
                        Select::make('from_level_id')
                            ->label('From Level (Current)')
                            ->relationship('fromLevel', 'name')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('This is automatically filled based on student\'s current level'),
                        
                        Select::make('to_level_id')
                            ->label('To Level (New)')
                            ->options(function (callable $get) {
                                return AcademicLevel::active()
                                    ->ordered()
                                    ->get()
                                    ->mapWithKeys(function ($level) {
                                        return [$level->id => $level->display_name];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->helperText('Select the new grade level for the student'),
                        
                        Select::make('promotion_type')
                            ->label('Promotion Type')
                            ->options([
                                'regular' => 'Regular Promotion (End of Year)',
                                'skip' => 'Skip Grade (Advancement)',
                                'repeat' => 'Repeat Grade',
                                'transfer' => 'Transfer from Another School',
                                'manual' => 'Manual Administrative Promotion',
                            ])
                            ->required()
                            ->default('regular')
                            ->helperText('Select the reason for this promotion'),
                        
                        TextInput::make('academic_year')
                            ->label('Academic Year')
                            ->placeholder('e.g., 2023-2024')
                            ->helperText('Optional: Academic year for this promotion'),
                        
                        DatePicker::make('effective_date')
                            ->label('Effective Date')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->helperText('When should this promotion take effect?'),
                    ])
                    ->columns(2),
                
                Section::make('Academic Performance')
                    ->schema([
                        TextInput::make('final_gpa')
                            ->label('Final GPA')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(4)
                            ->step(0.01)
                            ->placeholder('e.g., 3.75')
                            ->helperText('Optional: Student\'s GPA at time of promotion'),
                        
                        Textarea::make('promotion_notes')
                            ->label('Promotion Notes')
                            ->rows(4)
                            ->placeholder('Add any relevant notes about this promotion...')
                            ->helperText('Optional: Additional information about this promotion')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Section::make('Options')
                    ->schema([
                        Toggle::make('auto_update_enrollments')
                            ->label('Automatically Update Enrollments')
                            ->helperText('Complete enrollments for courses that don\'t match the new grade level')
                            ->default(true),
                    ]),
            ]);
    }
}
