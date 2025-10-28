<?php

namespace App\Filament\Student\Resources\Courses\Schemas;

use App\Models\Course;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Support\Enums\TextSize;

class CourseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Course Information')
                    ->schema([
                       ImageEntry::make('thumbnail')
                            ->label('Course Image')
                            ->columnSpanFull(),
                        
                       TextEntry::make('course_code')
                            ->label('Course Code')
                            ->size(TextSize::Large)
                            ->weight('bold'),
                        
                       TextEntry::make('title')
                            ->label('Course Title')
                            ->size(TextSize::Large)
                            ->columnSpanFull(),
                        
                       TextEntry::make('description')
                            ->html()
                            ->columnSpanFull(),
                        
                       TextEntry::make('category')
                            ->badge(),
                        
                       TextEntry::make('level')
                            ->badge(),
                        
                       TextEntry::make('duration_weeks')
                            ->suffix(' weeks'),
                        
                       TextEntry::make('credit_hours')
                            ->suffix(' hours'),
                    ])
                    ->columns(2),

               Section::make('My Progress')
                    ->schema([
                       TextEntry::make('enrollment.progress_percentage')
                            ->label('Overall Progress')
                            ->suffix('%')
                            ->color('success')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->getStateUsing(function ($record) {
                                $enrollment = $record->enrollments->first();
                                return $enrollment ? $enrollment->progress_percentage : 0;
                            }),
                        
                       TextEntry::make('enrollment.status')
                            ->label('Enrollment Status')
                            ->badge()
                            ->getStateUsing(function ($record) {
                                return $record->enrollments->first()?->status;
                            }),
                        
                       TextEntry::make('enrollment.enrolled_at')
                            ->label('Enrolled Since')
                            ->date('M d, Y')
                            ->getStateUsing(function ($record) {
                                return $record->enrollments->first()?->enrolled_at;
                            }),
                    ])
                    ->columns(3),

               Section::make('Instructors')
                    ->schema([
                       RepeatableEntry::make('instructors')
                            ->label('')
                            ->schema([
                               TextEntry::make('user.full_name')
                                    ->label('Name'),
                               TextEntry::make('user.email')
                                    ->label('Email')
                                    ->copyable(),
                               TextEntry::make('qualification')
                                    ->label('Qualification'),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),

               Section::make('Learning Objectives')
                    ->schema([
                       TextEntry::make('learning_objectives')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
