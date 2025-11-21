<?php

namespace App\Filament\Student\Resources\AvailableCourses\Schemas;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;

class AvailableCourseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Course Overview')
                    ->schema([
                        ImageEntry::make('thumbnail')
                            ->label('Course Image')
                            ->defaultImageUrl(asset('images/default-course.png'))
                            ->columnSpanFull(),
                        
                        TextEntry::make('course_code')
                            ->label('Course Code')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->copyable(),
                        
                        TextEntry::make('title')
                            ->label('Course Title')
                            ->size(TextSize::Large)
                            ->columnSpanFull(),
                        
                        TextEntry::make('description')
                            ->html()
                            ->columnSpanFull(),
                        
                        TextEntry::make('category')
                            ->badge()
                            ->color('info'),
                        
                        TextEntry::make('level')
                            ->badge()
                            ->color(fn ($state) => match($state) {
                                'beginner' => 'success',
                                'intermediate' => 'warning',
                                'advanced' => 'danger',
                            }),
                        
                        TextEntry::make('academicLevel.display_name')
                            ->label('Grade Level')
                            ->badge()
                            ->color('primary')
                            ->placeholder('All Grade Levels'),
                        
                        TextEntry::make('duration_weeks')
                            ->label('Duration')
                            ->suffix(' weeks'),
                        
                        TextEntry::make('credit_hours')
                            ->label('Credit Hours')
                            ->suffix(' hours'),
                    ])
                    ->columns(3),

                Section::make('Pricing & Schedule')
                    ->schema([
                        TextEntry::make('price_3x_weekly')
                            ->label('3 Times Per Week')
                            ->money('USD')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->color('success')
                            ->placeholder('Price not set')
                            ->helperText('Recommended for beginners'),
                        
                        TextEntry::make('price_5x_weekly')
                            ->label('5 Times Per Week')
                            ->money('USD')
                            ->size(TextSize::Large)
                            ->weight('bold')
                            ->color('success')
                            ->placeholder('Price not set')
                            ->helperText('Intensive learning schedule'),
                        
                        TextEntry::make('subscription_duration_weeks')
                            ->label('Subscription Duration')
                            ->suffix(' weeks')
                            ->helperText('Duration per subscription period'),
                    ])
                    ->columns(3),

                Section::make('Instructors')
                    ->schema([
                        RepeatableEntry::make('instructors')
                            ->label('')
                            ->schema([
                                TextEntry::make('user.full_name')
                                    ->label('Name')
                                    ->size(TextSize::Large),
                                TextEntry::make('qualification')
                                    ->label('Qualification'),
                                TextEntry::make('specialization')
                                    ->label('Specialization'),
                                TextEntry::make('years_of_experience')
                                    ->label('Experience')
                                    ->suffix(' years'),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Learning Objectives')
                    ->schema([
                        TextEntry::make('learning_objectives')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Section::make('Prerequisites')
                    ->schema([
                        TextEntry::make('prerequisites')
                            ->html()
                            ->columnSpanFull()
                            ->placeholder('No prerequisites required'),
                    ])
                    ->visible(fn ($record) => !empty($record->prerequisites))
                    ->collapsible()
                    ->collapsed(),

                Section::make('Course Statistics')
                    ->schema([
                        TextEntry::make('active_enrollments_count')
                            ->label('Current Students')
                            ->badge()
                            ->color('info')
                            ->getStateUsing(fn ($record) => $record->activeEnrollments()->count()),
                        
                        TextEntry::make('max_students')
                            ->label('Maximum Capacity')
                            ->badge()
                            ->placeholder('Unlimited'),
                        
                        TextEntry::make('available_spots')
                            ->label('Available Spots')
                            ->badge()
                            ->color('success')
                            ->getStateUsing(function ($record) {
                                $current = $record->activeEnrollments()->count();
                                $max = $record->max_students;
                                
                                if (!$max) return 'Unlimited';
                                
                                $available = $max - $current;
                                return $available > 0 ? $available : 'Full';
                            }),
                    ])
                    ->columns(3),
            ]);
    }
}