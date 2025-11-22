<?php

namespace App\Filament\Student\Resources\StudentPayments\Schemas;

use App\Models\Course;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\TextEntry;

class StudentPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        $student = Auth::user()->student;

        return $schema
            ->components([
                Section::make('Course Information')
                    ->description('Select the course you are paying for')
                    ->schema([
                        Select::make('course_id')
                            ->label('Select Course')
                            ->options(function () use ($student) {
                                // Get pending enrollment requests OR active enrollments
                                $pendingRequests = $student->enrollmentRequests()
                                    ->whereIn('status', ['pending', 'payment_pending'])
                                    ->with('course')
                                    ->get()
                                    ->pluck('course.title', 'course.id');
                                
                                $activeEnrollments = $student->courses()
                                    ->where('enrollments.status', 'active')
                                    ->where('courses.status', 'active')
                                    ->get()
                                    ->mapWithKeys(function ($course) {
                                        return [$course->id => "{$course->course_code} - {$course->title}"];
                                    });
                                
                                return $pendingRequests->merge($activeEnrollments)->unique();
                            })
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->helperText('Select the course you are making payment for'),
                        
                        Select::make('frequency')
                            ->label('Subscription Frequency')
                            ->options([
                                '3x_weekly' => '3 times per week',
                                '5x_weekly' => '5 times per week',
                            ])
                            ->required()
                            ->reactive()
                            ->helperText('How many classes per week?'),
                        
                        Placeholder::make('course_pricing')
                            ->label('Course Pricing')
                            ->content(function (callable $get) {
                                $courseId = $get('course_id');
                                $frequency = $get('frequency');
                                
                                if (!$courseId) {
                                    return 'Select a course to view pricing';
                                }
                                
                                $course = Course::find($courseId);
                                
                                if (!$course) {
                                    return 'Course not found';
                                }
                                
                                $price3x = $course->price_3x_weekly ? '$' . number_format($course->price_3x_weekly, 2) : 'Not available';
                                $price5x = $course->price_5x_weekly ? '$' . number_format($course->price_5x_weekly, 2) : 'Not available';
                                $duration = $course->subscription_duration_weeks ?? 4;
                                
                                $selectedPrice = match($frequency) {
                                    '3x_weekly' => $price3x,
                                    '5x_weekly' => $price5x,
                                    default => 'Select frequency',
                                };
                                
                                return "3x Weekly: {$price3x}\n" .
                                       "5x Weekly: {$price5x}\n" .
                                       "Duration: {$duration} weeks\n" .
                                       "Selected: {$selectedPrice}";
                            })
                            ->columnSpanFull()
                            ->visible(fn (callable $get) => $get('course_id')),
                    ])
                    ->columns(2),
                
                Section::make('Payment Details')
                    ->schema([
                        TextInput::make('amount')
                            ->label('Amount Paid')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->helperText('Enter the exact amount you paid'),
                        
                        Select::make('currency')
                            ->label('Currency')
                            ->options([
                                'USD' => 'USD ($)',
                                'EUR' => 'EUR (€)',
                                'GBP' => 'GBP (£)',
                                'NGN' => 'NGN (₦)',
                            ])
                            ->default('USD')
                            ->required(),
                        
                        DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->required()
                            ->native(false)
                            ->maxDate(now())
                            ->helperText('When did you make this payment?'),
                        
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'bank_transfer' => 'Bank Transfer',
                                'credit_card' => 'Credit Card',
                                'cash' => 'Cash',
                                'mobile_money' => 'Mobile Money',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->default('bank_transfer'),
                    ])
                    ->columns(2),
                
                Section::make('Upload Receipt')
                    ->description('Upload proof of payment')
                    ->schema([
                        FileUpload::make('receipt_path')
                            ->label('Payment Receipt')
                            ->directory('student-payment-receipts')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120) // 5MB
                            ->required()
                            ->helperText('Upload a clear image or PDF of your payment receipt (max 5MB)')
                            ->columnSpanFull(),
                        
                        Textarea::make('parent_notes')
                            ->label('Additional Notes (Optional)')
                            ->rows(4)
                            ->placeholder('Add any additional information about this payment...')
                            ->helperText('e.g., Transaction reference, special instructions')
                            ->columnSpanFull(),
                    ]),
                
                Section::make('Important Notice')
                    ->schema([
                        TextEntry::make('notice')
                            ->label('')
                            ->state('After uploading your payment receipt:
                            • Administration will review and verify your payment
                            • This typically takes 1-2 business days
                            • You will be notified once your payment is verified
                            • Your course enrollment/subscription will be activated upon verification
                            • You can track the status of your payment in the "My Payments" section')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }
}