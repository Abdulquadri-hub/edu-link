<?php

namespace App\Filament\Admin\Resources\ChildLinkingRequests\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ChildLinkingRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('parent_id')
                    ->relationship('parent', 'id')
                    ->required(),
                Select::make('student_id')
                    ->relationship('student', 'id')
                    ->required(),
                TextInput::make('relationship')
                    ->required(),
                Toggle::make('is_primary_contact')
                    ->required(),
                Toggle::make('can_view_grades')
                    ->required(),
                Toggle::make('can_view_attendance')
                    ->required(),
                Textarea::make('parent_message')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                    ->default('pending')
                    ->required(),
                Textarea::make('admin_notes')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('reviewed_by')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('reviewed_at'),
            ]);
    }
}
