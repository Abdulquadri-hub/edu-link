<?php

namespace App\Filament\Instructor\Resources\ClassSessions\Schemas;

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Set;

class ClassSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Session Details')
                    ->schema([
                        Select::make('course_id')
                            ->relationship('course', 'title', function ($query) {
                                $query->whereHas('instructors', function ($q) {
                                    $q->where('instructor_course.instructor_id', Auth::user()->instructor->id);
                                });
                            })
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Introduction to Laravel'),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Brief description of what will be covered...'),
                    ])
                    ->columns(2),

                Section::make('Schedule')
                    ->schema([
                        DateTimePicker::make('scheduled_at')
                            ->required()
                            ->native(false)
                            ->minDate(now())
                            ->label('Scheduled Date & Time'),
                        TextInput::make('max_participants')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Leave blank for unlimited'),
                    ])
                    ->columns(2),

                Section::make('Online Meeting')
                    ->schema([
                        TextInput::make('google_meet_link')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://meet.google.com/xxx-xxxx-xxx')
                            ->helperText('Generate or paste your Google Meet link')
                            ->suffixAction(
                                Action::make('generate')
                                    ->icon('heroicon-o-sparkles')
                                    ->action(function (Set $set) {
                                        $set('google_meet_link', 'https://meet.google.com/' . uniqid());
                                        Notification::make()
                                            ->success()
                                            ->title('Meet link generated')
                                            ->send();
                                    })
                            ),
                        Textarea::make('notes')
                            ->rows(3)
                            ->placeholder('Additional notes for this session...'),
                    ])
                    ->columns(1),
            ]);
    }
}
