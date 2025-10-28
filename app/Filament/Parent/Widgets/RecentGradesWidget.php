<?php

namespace App\Filament\Parent\Widgets;

use App\Models\Grade;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class RecentGradesWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        $parent = Auth::user()->parent;

        return $table
            ->query(fn (): Builder => Grade::query()
                ->whereHas('submission.student.parents', function ($query) use ($parent) {
                        $query->where('student_parent.parent_id', $parent->id)
                              ->where('can_view_grades', true);
                    })
                    ->where('is_published', true)
                    ->orderBy('published_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('submission.student.user.full_name')
                    ->label('Child'),
                TextColumn::make('submission.assignment.title')
                    ->label('Assignment')
                    ->limit(30),
                TextColumn::make('submission.assignment.course.course_code')
                    ->label('Course'),
                TextColumn::make('percentage')
                    ->suffix('%')
                    ->color(fn ($state) => match(true) {
                        $state >= 80 => 'success',
                        $state >= 70 => 'info',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    })
                    ->weight('bold'),
                TextColumn::make('letter_grade')
                    ->badge(),
                TextColumn::make('published_at')
                    ->dateTime('M d')
                    ->description(fn ($record) => $record->published_at->diffForHumans()),
            ]);
    }
}
