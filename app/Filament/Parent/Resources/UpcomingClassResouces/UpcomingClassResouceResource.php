<?php

namespace App\Filament\Parent\Resources\UpcomingClassResouces;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use App\Models\ClassSession;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Models\UpcomingClassResouce;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Parent\Resources\UpcomingClassResouces\Pages\EditUpcomingClassResouce;
use App\Filament\Parent\Resources\UpcomingClassResouces\Pages\ViewUpcomingClassResouce;
use App\Filament\Parent\Resources\UpcomingClassResouces\Pages\ListUpcomingClassResouces;
use App\Filament\Parent\Resources\UpcomingClassResouces\Pages\CreateUpcomingClassResouce;
use App\Filament\Parent\Resources\UpcomingClassResouces\Schemas\UpcomingClassResouceForm;
use App\Filament\Parent\Resources\UpcomingClassResouces\Tables\UpcomingClassResoucesTable;
use App\Filament\Parent\Resources\UpcomingClassResouces\Schemas\UpcomingClassResouceInfolist;

class UpcomingClassResouceResource extends Resource
{
    protected static ?string $model = ClassSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Clock;

    protected static string|UnitEnum|null $navigationGroup = 'Academic';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Upcoming Classes';

    public static function getEloquentQuery(): Builder
    {
        $parent = Auth::user()->parent;
        
        return parent::getEloquentQuery()
            ->whereHas('course.enrollments.student.parents', function ($query) use ($parent) {
                $query->where('parents.parent_id', $parent->id);
            })
            ->whereIn('status', ['scheduled', 'in-progress'])
            ->where('scheduled_at', '>', now()->subHours(2))
            ->with(['course', 'instructor.user']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return UpcomingClassResouceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UpcomingClassResoucesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUpcomingClassResouces::route('/'),
            'view' => ViewUpcomingClassResouce::route('/{record}'),
          
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $parent = Auth::user()->parent;
        
        $count = static::getModel()::whereHas('course.enrollments.student.parents', function ($query) use ($parent) {
            $query->where('parents.parent_id', $parent->id);
        })
        ->whereIn('status', ['scheduled', 'in-progress'])
        ->where('scheduled_at', '>', now())
        ->where('scheduled_at', '<', now()->addDay())
        ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}
