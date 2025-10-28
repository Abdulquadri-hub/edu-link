<?php

namespace App\Filament\Instructor\Resources\Materials\Tables;

use App\Models\Material;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class MaterialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->wrap()
                    ->description(fn ($record) => $record->description ? Str::limit($record->description, 50) : null),
                
                TextColumn::make('course.course_code')
                    ->searchable()
                    ->sortable()
                    ->label('Course'),
                
                TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'danger' => 'pdf',
                        'warning' => 'video',
                        'success' => 'slide',
                        'info' => 'link',
                        'primary' => 'document',
                        'gray' => 'other',
                    ])
                    ->icon(fn ($state) => match($state) {
                        'pdf' => 'heroicon-o-document-text',
                        'video' => 'heroicon-o-video-camera',
                        'slide' => 'heroicon-o-presentation-chart-bar',
                        'document' => 'heroicon-o-document',
                        'link' => 'heroicon-o-link',
                        default => 'heroicon-o-folder',
                    }),
                
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'danger' => 'archived',
                    ]),
                
                TextColumn::make('file_size')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return 'N/A';
                        $units = ['B', 'KB', 'MB', 'GB'];
                        $size = $state;
                        $unit = 0;
                        while ($size >= 1024 && $unit < count($units) - 1) {
                            $size /= 1024;
                            $unit++;
                        }
                        return round($size, 2) . ' ' . $units[$unit];
                    })
                    ->label('Size')
                    ->toggleable(),
                
                TextColumn::make('download_count')
                    ->sortable()
                    ->label('Downloads')
                    ->badge()
                    ->color('success'),
                
                IconColumn::make('is_downloadable')
                    ->boolean()
                    ->label('Downloadable')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('uploaded_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->description(fn ($record) => $record->uploaded_at->diffForHumans()),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'pdf' => 'PDF',
                        'video' => 'Video',
                        'slide' => 'Slide',
                        'document' => 'Document',
                        'link' => 'Link',
                        'other' => 'Other',
                    ]),
                
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
                
                SelectFilter::make('course')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
                
                TernaryFilter::make('is_downloadable')
                    ->label('Downloadable')
                    ->trueLabel('Downloadable only')
                    ->falseLabel('View only')
                    ->placeholder('All materials'),
            ])
            ->recordActions([
                 Action::make('publish')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Publish Material')
                    ->modalDescription('Students will be able to access this material immediately.')
                    ->action(function (Material $record) {
                        $record->update(['status' => 'published']);
                        Notification::make()
                            ->success()
                            ->title('Material published')
                            ->body('Students can now access this material')
                            ->send();
                    })
                    ->visible(fn (Material $record) => $record->status === 'draft'),
                
                Action::make('unpublish')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Unpublish Material')
                    ->modalDescription('Students will no longer be able to access this material.')
                    ->action(function (Material $record) {
                        $record->update(['status' => 'draft']);
                        Notification::make()
                            ->success()
                            ->title('Material unpublished')
                            ->body('Material is now hidden from students')
                            ->send();
                    })
                    ->visible(fn (Material $record) => $record->status === 'published'),
                
                Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->label('Preview/Download')
                    ->url(fn (Material $record) => 
                        $record->hasExternalUrl() 
                            ? $record->external_url 
                            : asset('storage/' . $record->file_path)
                    )
                    ->openUrlInNewTab(),
                
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
