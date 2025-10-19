<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make("Personal Information")->schema([
                    TextInput::make('first_name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('last_name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('username')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    TextInput::make('email')   
                        ->email()
                        ->autocomplete(true)
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    TextInput::make('phone')
                       ->tel()
                       ->maxLength(255),
                    FileUpload::make('avatar')->image()->directory('avatars')->imageEditor(true),
                ])
                ->columns(2),

                Section::make("Account Settings")->schema([
                    Select::make('user_type')->options([
                        'admin' => 'Admin',
                        'instructor' => 'Instructor',
                        'student' => 'Student',
                        'parent' => 'Parent'
                    ])
                    ->required()
                    ->native(false)
                    ->searchable(),

                    Select::make('status')->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended'
                    ])
                    ->required()
                    ->native(false)
                    ->default('active')
                    ->searchable(),

                    TextInput::make('password')
                       ->password()
                       ->dehydrateStateUsing(
                            fn ($state) => Hash::make($state)
                        )
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $context) : bool => $context === 'create')
                        ->maxLength(255),

                    DateTimePicker::make('email_verified_at')
                        ->native(false),
                ])
                ->columns(2),
            ]);
    }
}
