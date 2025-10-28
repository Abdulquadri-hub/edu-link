<?php

namespace App\Filament\Student\Widgets;

use Filament\Tables\Table;
use App\Models\ClassSession;
use Filament\Actions\Action;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class UpcomingClassesWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;


     public function table(Table $table): Table
    {
        $student = Auth::user()->student;

        return $table
            ->query(
                ClassSession::query()
                    ->whereHas('course.enrollments', function ($query) use ($student) {
                        $query->where('student_id', $student->id)->where('status', 'active');
                    })
                    ->where('scheduled_at', '>', now())
                    ->where('status', 'scheduled')
                    ->orderBy('scheduled_at')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('title')
                    ->limit(40),
                TextColumn::make('course.course_code')
                    ->label('Course'),
                TextColumn::make('scheduled_at')
                    ->dateTime('M d - H:i')
                    ->description(fn ($record) => $record->scheduled_at->diffForHumans()),
                IconColumn::make('google_meet_link')
                    ->boolean()
                    ->label('Join Link'),
            ])
            ->recordActions([
                Action::make('join')
                    ->icon('heroicon-o-video-camera')
                    ->url(fn ($record) => $record->google_meet_link)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->google_meet_link)),
            ]);
    }

    protected function getTableHeading(): string
    {
        return 'Upcoming Classes';
    }
}
