<?php

namespace App\Filament\Student\Resources\MyEnrollments\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;

class MyEnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn($query) => $query->where('student_id', Auth::user()->student->id))
            ->columns([
                TextColumn::make('course.title')->label('Course'),
                BadgeColumn::make('status'),
                TextColumn::make('price')->money('usd')->label('Monthly Fee'),
                TextColumn::make('frequency')->label('Frequency'),
                TextColumn::make('enrolled_at')->date()->label('Enrolled At'),
            ])
            ->recordActions([
                Action::make('upload_receipt')
                    ->label('Upload Receipt')
                    ->icon('heroicon-o-upload')
                    ->form([
                        FileUpload::make('receipt')
                            ->label('Payment Receipt')
                            ->maxSize(10240)
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->required(),
                    ])
                    ->action(function (Enrollment $record, $data) {
                        $notes = $record->notes ?? [];
                        // Save receipt filepath into notes
                        $notes['receipt'] = $data['receipt'] ?? null;
                        $record->update(['notes' => $notes]);
                        Notification::make()->success()->title('Receipt Uploaded')->body('Your payment receipt has been uploaded and is pending admin approval.')->send();
                    })
                    ->visible(fn($record) => $record->status === 'pending_payment'),
            ]);
    }
}
