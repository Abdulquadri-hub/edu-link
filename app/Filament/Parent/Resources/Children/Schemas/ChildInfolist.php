<?php

namespace App\Filament\Parent\Resources\Children\Schemas;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;

class ChildInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Student Information')
                    ->schema([
                        ImageEntry::make('user.avatar')
                            ->label('Photo')
                            ->circular()
                            ->defaultImageUrl(asset('images/default-avatar.png')),
                        
                        TextEntry::make('student_id')
                            ->label('Student ID')
                            ->copyable()
                            ->size(TextSize::Large)
                            ->weight('bold'),
                        
                        TextEntry::make('user.full_name')
                            ->label('Full Name')
                            ->size(TextSize::Large),
                        
                        TextEntry::make('user.email')
                            ->label('Email')
                            ->copyable()
                            ->icon('heroicon-o-envelope'),
                        
                        TextEntry::make('user.phone')
                            ->label('Phone')
                            ->icon('heroicon-o-phone'),
                        
                        TextEntry::make('gender')
                            ->badge(),
                        
                        TextEntry::make('date_of_birth')
                            ->date('M d, Y')
                            ->helperText(fn ($record) => 'Age: ' . $record->age . ' years'),
                        
                        TextEntry::make('enrollment_status')
                            ->badge()
                            ->color(fn ($state) => match($state) {
                                'active' => 'success',
                                'graduated' => 'info',
                                'dropped' => 'warning',
                                'suspended' => 'danger',
                            }),
                    ])
                    ->columns(3),

               Section::make('Academic Performance')
                    ->schema([
                       TextEntry::make('enrolled_courses')
                            ->label('Active Courses')
                            ->getStateUsing(fn ($record) => $record->activeEnrollments->count())
                            ->badge()
                            ->color('info'),
                        
                       TextEntry::make('overall_progress')
                            ->label('Overall Progress')
                            ->getStateUsing(fn ($record) => round($record->calculateOverallProgress(), 1) . '%')
                            ->badge()
                            ->color('success'),
                        
                       TextEntry::make('attendance_rate')
                            ->label('Attendance Rate')
                            ->getStateUsing(fn ($record) => round($record->calculateAttendanceRate(), 1) . '%')
                            ->badge()
                            ->color(fn ($state) => floatval($state) >= 85 ? 'success' : 'warning'),
                        
                       TextEntry::make('average_grade')
                            ->label('Average Grade')
                            ->getStateUsing(function ($record) {
                                $grades = $record->grades()
                                    ->where('is_published', true)
                                    ->avg('percentage');
                                return $grades ? round($grades, 1) . '%' : 'No grades yet';
                            })
                            ->badge()
                            ->color(fn ($state) => match(true) {
                                str_contains($state, 'No') => 'gray',
                                floatval($state) >= 80 => 'success',
                                floatval($state) >= 70 => 'info',
                                floatval($state) >= 60 => 'warning',
                                default => 'danger',
                            }),
                    ])
                    ->columns(4),

               Section::make('Current Enrollments')
                    ->schema([
                       RepeatableEntry::make('activeEnrollments')
                            ->label('')
                            ->schema([
                               TextEntry::make('course.course_code')
                                    ->label('Course Code'),
                               TextEntry::make('course.title')
                                    ->label('Course Title')
                                    ->limit(30),
                               TextEntry::make('progress_percentage')
                                    ->label('Progress')
                                    ->suffix('%')
                                    ->color('success'),
                               TextEntry::make('status')
                                    ->badge(),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ]),

               Section::make('Emergency Contact')
                    ->schema([
                       TextEntry::make('emergency_contact_name')
                            ->label('Contact Name')
                            ->icon('heroicon-o-user'),
                       TextEntry::make('emergency_contact_phone')
                            ->label('Contact Phone')
                            ->icon('heroicon-o-phone')
                            ->copyable(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
