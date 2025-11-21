<?php

namespace App\Filament\Admin\Resources\PaymentVerifications\Tables;

use Filament\Tables\Table;
use App\Models\Subscription;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class PaymentVerificationsTable
{
    public static function configure(Table $table): Table
    {
       return $table
            ->columns([
                TextColumn::make('payment_reference')
                    ->label('Reference')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                
                TextColumn::make('parent.user.full_name')
                    ->label('Parent')
                    ->searchable(['first_name', 'last_name'])
                    ->description(fn ($record) => $record->parent->user->email),
                
                TextColumn::make('student.user.full_name')
                    ->label('Student')
                    ->searchable(['first_name', 'last_name'])
                    ->description(fn ($record) => $record->student->student_id),
                
                TextColumn::make('course.course_code')
                    ->label('Course')
                    ->searchable(),
                
                TextColumn::make('amount')
                    ->money(fn ($record) => $record->currency)
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('payment_date')
                    ->date('M d, Y')
                    ->sortable(),
                
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'verified',
                        'danger' => 'rejected',
                    ])
                    ->sortable(),
                
                IconColumn::make('subscription')
                    ->label('Sub')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->hasSubscription())
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                
                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->description(fn ($record) => $record->created_at->diffForHumans()),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),
                
                Filter::make('pending_only')
                    ->label('Pending Only')
                    ->query(fn (Builder $query) => $query->where('status', 'pending'))
                    ->default(),
                
                Filter::make('no_subscription')
                    ->label('No Subscription Created')
                    ->query(fn (Builder $query) => $query->whereDoesntHave('subscription')),
            ])
            ->recordActions([
                Action::make('verify_create_subscription')
                    ->label('Verify & Create Subscription')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->schema([
                        Select::make('frequency')
                            ->label('Subscription Frequency')
                            ->options([
                                '3x_weekly' => '3 times per week',
                                '5x_weekly' => '5 times per week',
                            ])
                            ->required()
                            ->default('3x_weekly')
                            ->helperText('This determines the number of sessions per week'),
                        
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->default(now())
                            ->native(false),
                        
                        TextInput::make('duration_weeks')
                            ->label('Duration (Weeks)')
                            ->required()
                            ->numeric()
                            ->default(fn ($record) => $record->course->subscription_duration_weeks ?? 4)
                            ->minValue(1)
                            ->helperText('Subscription duration in weeks'),
                        
                        Textarea::make('admin_notes')
                            ->label('Admin Notes (Optional)')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        // Verify payment
                        $record->verify(Auth::id(), $data['admin_notes'] ?? null);
                        
                        // Calculate end date
                        $startDate = \Carbon\Carbon::parse($data['start_date']);
                        $endDate = $startDate->copy()->addWeeks($data['duration_weeks']);
                        
                        // Create subscription
                        $subscription = Subscription::create([
                            'student_id' => $record->student_id,
                            'course_id' => $record->course_id,
                            'payment_id' => $record->id,
                            'frequency' => $data['frequency'],
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'status' => 'active',
                        ]);
                        
                        Notification::make()
                            ->success()
                            ->title('Payment Verified')
                            ->body("Payment verified and subscription {$subscription->subscription_code} created successfully.")
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),
                
                Action::make('verify_only')
                    ->label('Verify Only')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('admin_notes')
                            ->label('Admin Notes')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->verify(Auth::id(), $data['admin_notes'] ?? null);
                        
                        Notification::make()
                            ->success()
                            ->title('Payment Verified')
                            ->body('Payment has been verified without creating a subscription.')
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),
                
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('reason')
                            ->label('Reason for Rejection')
                            ->required()
                            ->rows(3)
                            ->placeholder('Explain why this payment is being rejected...'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->reject(Auth::id(), $data['reason']);
                        
                        Notification::make()
                            ->success()
                            ->title('Payment Rejected')
                            ->body('The payment has been rejected.')
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),
                
                Action::make('create_subscription')
                    ->label('Create Subscription')
                    ->icon('heroicon-o-document-plus')
                    ->color('warning')
                    ->schema([
                        Select::make('frequency')
                            ->label('Subscription Frequency')
                            ->options([
                                '3x_weekly' => '3 times per week',
                                '5x_weekly' => '5 times per week',
                            ])
                            ->required()
                            ->default('3x_weekly'),
                        
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->default(now())
                            ->native(false),
                        
                        TextInput::make('duration_weeks')
                            ->label('Duration (Weeks)')
                            ->required()
                            ->numeric()
                            ->default(fn ($record) => $record->course->subscription_duration_weeks ?? 4)
                            ->minValue(1),
                    ])
                    ->action(function ($record, array $data) {
                        $startDate = \Carbon\Carbon::parse($data['start_date']);
                        $endDate = $startDate->copy()->addWeeks($data['duration_weeks']);
                        
                        $subscription = Subscription::create([
                            'student_id' => $record->student_id,
                            'course_id' => $record->course_id,
                            'payment_id' => $record->id,
                            'frequency' => $data['frequency'],
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'status' => 'active',
                        ]);
                        
                        Notification::make()
                            ->success()
                            ->title('Subscription Created')
                            ->body("Subscription {$subscription->subscription_code} created successfully.")
                            ->send();
                    })
                    ->visible(fn ($record) => $record->isVerified() && !$record->hasSubscription()),
                
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }
}
