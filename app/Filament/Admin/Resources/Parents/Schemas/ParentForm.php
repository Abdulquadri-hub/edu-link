<?php

namespace App\Filament\Admin\Resources\Parents\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class ParentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Parent Information')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'email', 
                                fn ($query) => $query->where('user_type', 'parent')
                            )
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('parent_id')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'PAR' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT))
                            ->maxLength(255),
                        TextInput::make('occupation')
                            ->maxLength(255),
                        TextInput::make('secondary_phone')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Contact Information')
                    ->schema([
                        Textarea::make('address')
                            ->rows(2)
                            ->columnSpanFull(),
                        TextInput::make('city')
                            ->maxLength(255),
                        TextInput::make('state')
                            ->maxLength(255),
                        TextInput::make('country')
                            ->default('Nigeria')
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Section::make('Preferences')
                    ->schema([
                        Select::make('preferred_contact_method')
                            ->options([
                                'email' => 'Email',
                                'phone' => 'Phone',
                                'sms' => 'SMS',
                            ])
                            ->required()
                            ->native(false)
                            ->default('email'),
                        Toggle::make('receives_weekly_report')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),
            ]);
    }
}
