<?php

namespace App\Filament\Student\Resources\MyEnrollmentRequests\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Auth;
use App\Services\StudentService;

class MyEnrollmentRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Enrollment Details')
                    ->columns(2)
                    ->schema([
                        Select::make('course_id')
                            ->label('Course')
                            ->relationship('course', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('frequency_preference')
                            ->label('Preferred Frequency')
                            ->options([
                                '3x_weekly' => '3 times per week',
                                '5x_weekly' => '5 times per week',
                            ])
                            ->required(),

                        Textarea::make('student_message')
                            ->label('Message to Admin (Optional)')
                            ->placeholder('Any specific requests or information regarding your enrollment.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Parent/Guardian Information')
                    ->description('As a minor without a linked parent, you must provide parent information to proceed.')
                    ->visible(function () {
                        $student = Auth::user()->student;
                        $studentService = app(StudentService::class);
                        $isMinor = $studentService->isMinor($student->id);
                        $hasParent = $student->parents()->exists();
                        return $isMinor && !$hasParent;
                    })
                    ->columns(2)
                    ->schema([
                        TextInput::make('new_parent_first_name')
                            ->label('Parent First Name')
                            ->required(),
                        TextInput::make('new_parent_last_name')
                            ->label('Parent Last Name')
                            ->required(),
                        TextInput::make('new_parent_email')
                            ->label('Parent Email')
                            ->email()
                            ->required(),
                        TextInput::make('new_parent_phone')
                            ->label('Parent Phone')
                            ->tel()
                            ->required(),
                        TextInput::make('new_parent_relationship')
                            ->label('Relationship to Student')
                            ->required(),
                        TextInput::make('new_parent_occupation')
                            ->label('Parent Occupation'),
                        TextInput::make('new_parent_address')
                            ->label('Address')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('new_parent_city')
                            ->label('City')
                            ->required(),
                        TextInput::make('new_parent_state')
                            ->label('State')
                            ->required(),
                        TextInput::make('new_parent_country')
                            ->label('Country')
                            ->required(),
                    ]),
            ]);
    }
}
