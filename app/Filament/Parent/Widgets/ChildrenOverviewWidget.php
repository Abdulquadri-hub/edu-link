<?php

namespace App\Filament\Parent\Widgets;

use App\Models\Student;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;

class ChildrenOverviewWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
         $parent = Auth::user()->parent;
        return $table
            ->query(fn (): Builder => Student::query()
                 ->whereHas('parents', function ($query) use ($parent) {
                        $query->where('student_parent.parent_id', $parent->id);
                    })
                    ->with(['user', 'enrollments'])
            )
            ->columns([
                ImageColumn::make('user.avatar')
                    ->label('Photo')
                    ->circular(),
                TextColumn::make('user.full_name')
                    ->label('Name'),
                TextColumn::make('activeEnrollments_count')
                    ->counts('activeEnrollments')
                    ->label('Courses')
                    ->badge(),
                TextColumn::make('progress')
                    ->label('Progress')
                    ->getStateUsing(fn ($record) => round($record->calculateOverallProgress(), 1) . '%')
                    ->color('success'),
                TextColumn::make('attendance')
                    ->label('Attendance')
                    ->getStateUsing(fn ($record) => round($record->calculateAttendanceRate(), 1) . '%')
                    ->color(fn ($state) => floatval($state) >= 85 ? 'success' : 'warning'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.parent.resources.children.view', ['record' => $record->id])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    protected function getTableHeading(): string
    {
        return 'Children Overview';
    }
}
