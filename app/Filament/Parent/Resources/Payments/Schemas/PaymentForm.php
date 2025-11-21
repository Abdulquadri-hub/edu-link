<?php

namespace App\Filament\Parent\Resources\Payments\Schemas;

use App\Models\Course;
use App\Models\Student;
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

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        $parent = Auth::user()->parent;

        return $schema
            ->components([
                Section::make('Student & Course Information')
                    ->description('Select the child and course for this payment')
                    ->schema([
                        Select::make('student_id')
                            ->label('Select Child')
                            ->options(function () use ($parent) {
                                return $parent->children()
                                    ->where('enrollment_status', 'active')
                                    ->get()
                                    ->pluck('user.full_name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('course_id', null))
                            ->helperText('Select which child this payment is for'),
                        
                        Select::make('course_id')
                            ->label('Select Course')
                            ->options(function (callable $get) {
                                $studentId = $get('student_id');
                                
                                if (!$studentId) {
                                    return [];
                                }
                                
                                $student = Student::find($studentId);
                                
                                // Get courses where student is enrolled
                                return $student->courses()
                                    ->where('enrollments.status', 'active')
                                    ->where('courses.status', 'active')
                                    ->get()
                                    ->mapWithKeys(function ($course) {
                                        return [$course->id => "{$course->course_code} - {$course->title}"];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->helperText('Select the course you are paying for')
                            ->disabled(fn (callable $get) => !$get('student_id')),
                        
                        Select::make('frequency')
                            ->label('Subscription Frequency')
                            ->options([
                                '3x_weekly' => '3 times per week',
                                '5x_weekly' => '5 times per week',
                            ])
                            ->required()
                            ->reactive()
                            ->helperText('How many classes per week?'),
                        
                        TextEntry::make('course_pricing')
                            ->label('Course Pricing')
                            ->state(function (callable $get) {
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
                            ->directory('payment-receipts')
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
            ]);
    }
}
