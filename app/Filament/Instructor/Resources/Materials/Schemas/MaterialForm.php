<?php

namespace App\Filament\Instructor\Resources\Materials\Schemas;

use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class MaterialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Material Information')
                    ->description('Upload or link learning materials for your courses')
                    ->schema([
                        Select::make('course_id')
                            ->label('Course')
                            ->relationship('course', 'title', function ($query) {
                                $query->whereHas('instructors', function ($q) {
                                    $q->where('instructor_course.instructor_id', Auth::user()->instructor->id);
                                });
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Select the course this material belongs to'),
                        
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Week 1 Lecture Slides - Introduction to Laravel')
                            ->columnSpanFull(),
                        
                        Textarea::make('description')
                            ->rows(3)
                            ->placeholder('Brief description of what this material covers...')
                            ->columnSpanFull()
                            ->helperText('Help students understand what they will learn from this material'),
                    ]),

                Section::make('Material Type & Upload')
                    ->schema([
                        Select::make('type')
                            ->options([
                                'pdf' => '📄 PDF Document',
                                'video' => '🎥 Video',
                                'slide' => '📊 Presentation/Slides',
                                'document' => '📝 Document (Word, etc.)',
                                'link' => '🔗 External Link',
                                'other' => '📦 Other',
                            ])
                            ->required()
                            ->native(false)
                            ->live()
                            ->default('pdf')
                            ->helperText('Choose the type of material you\'re uploading'),
                        
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft (Not visible to students)',
                                'published' => 'Published (Visible to students)',
                                'archived' => 'Archived',
                            ])
                            ->required()
                            ->native(false)
                            ->default('draft')
                            ->helperText('Only published materials are visible to students'),
                        
                        FileUpload::make('file_path')
                            ->label('Upload File')
                            ->directory('course-materials')
                            ->maxSize(102400) // 100MB
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/*',
                                'video/*',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-powerpoint',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            ])
                            ->visible(fn (Get $get) => $get('type') !== 'link')
                            // ->afterStateUpdated(function ($state, Set $set, $livewire, $component) {
                            //     $uploadedFile = $component->getUploaded;
                            //     if ($state) {
                            //         $set('file_name', $uploadedFile->getClientOriginalName());
                            //         $set('file_size', $uploadedFile->getSize());
                            //     }
                            // })
                            ->helperText('Max file size: 100MB')
                            ->columnSpanFull()
                            ->disk('public')
                            ->preserveFilenames()
                            ->uploadingMessage('Uploading material...'),
                            // ->successNotification('Material uploaded successfully'),
                        
                        TextInput::make('external_url')
                            ->label('External URL')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://www.youtube.com/watch?v=... or https://drive.google.com/...')
                            ->visible(fn (Get $get) => in_array($get('type'), ['link', 'video']))
                            ->helperText('Link to YouTube, Google Drive, or other external resources')
                            ->columnSpanFull(),
                        
                        Toggle::make('is_downloadable')
                            ->label('Allow students to download this file')
                            ->default(true)
                            ->visible(fn (Get $get) => $get('type') !== 'link')
                            ->helperText('If disabled, students can only view online')
                            ->inline(false),
                    ])
                    ->columns(2),
            ]);
    }
}
