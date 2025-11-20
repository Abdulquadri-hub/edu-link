I'll help you build the email verification feature for students, instructors, and parents. Based on your Laravel application structure, here's a comprehensive implementation:

## 1. First, update your User Model

```php
<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'username',
        'password',
        'phone',
        'role',
        'email_verified_at', // Make sure this is fillable
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ... rest of your existing code
}
```

## 2. Create Email Verification Notification

```php
<?php
// app/Notifications/VerifyEmailNotification.php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return url()->temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        
        $role = ucfirst($notifiable->role);

        return (new MailMessage)
            ->subject('Verify Your Email Address - EduLink')
            ->greeting("Hello {$notifiable->first_name}!")
            ->line("Thank you for registering as a {$role} with EduLink.")
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('This verification link will expire in 60 minutes.')
            ->line('If you did not create an account, no further action is required.')
            ->salutation('Best regards, The EduLink Team');
    }
}
```

## 3. Update User Model to use Custom Notification

```php
<?php
// Add this method to your User model (app/Models/User.php)

use App\Notifications\VerifyEmailNotification;

public function sendEmailVerificationNotification()
{
    $this->notify(new VerifyEmailNotification());
}
```

## 4. Create Verification Controller

```php
<?php
// app/Http/Controllers/Auth/VerificationController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VerificationController extends Controller
{
    /**
     * Show the email verification notice.
     */
    public function notice()
    {
        return Inertia::render('Auth/VerifyEmail', [
            'status' => session('status'),
        ]);
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectBasedOnRole($request->user());
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return $this->redirectBasedOnRole($request->user())
            ->with('status', 'email-verified');
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectBasedOnRole($request->user());
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Redirect user based on their role.
     */
    protected function redirectBasedOnRole($user)
    {
        return match($user->role) {
            'admin' => redirect()->intended('/admin'),
            'instructor' => redirect()->intended('/instructor'),
            'student' => redirect()->intended('/student'),
            'parent' => redirect()->intended('/parent'),
            default => redirect()->intended('/'),
        };
    }
}
```

## 5. Update Registration Controller

```php
<?php
// app/Http/Controllers/Auth/RegisterController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\ParentModel;
use App\Models\Instructor;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function index()
    {
        return Inertia::render('Register');
    }

    public function save(Request $request)
    {
        // Your existing validation
        $validated = $request->validate([
            'role' => 'required|in:student,parent,instructor',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'agrees_to_terms' => 'required|accepted',
            
            // Student fields
            'date_of_birth' => 'required_if:role,student|nullable|date',
            'gender' => 'required_if:role,student|nullable|in:male,female,other',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            
            // Parent fields
            'relationship' => 'required_if:role,parent|nullable|in:father,mother,guardian,other',
            'occupation' => 'nullable|string',
            'secondary_phone' => 'nullable|string',
            'preferred_contact_method' => 'nullable|in:email,phone,sms',
            'receives_weekly_report' => 'nullable|boolean',
            
            // Instructor fields
            'qualification' => 'required_if:role,instructor|nullable|string',
            'specialization' => 'nullable|string',
            'years_of_experience' => 'nullable|integer|min:0',
            'linkedin_url' => 'nullable|url',
            'bio' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'role' => $validated['role'],
                // email_verified_at will be null until verified
            ]);

            // Create role-specific record
            match($validated['role']) {
                'student' => $this->createStudent($user, $validated),
                'parent' => $this->createParent($user, $validated),
                'instructor' => $this->createInstructor($user, $validated),
            };

            DB::commit();

            // Fire the Registered event (triggers verification email)
            event(new Registered($user));

            // Log the user in
            Auth::login($user);

            // Redirect to verification notice
            return redirect()->route('verification.notice')
                ->with('status', 'Registration successful! Please check your email to verify your account.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Registration failed. Please try again.'
            ])->withInput();
        }
    }

    protected function createStudent(User $user, array $data)
    {
        return Student::create([
            'user_id' => $user->id,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? 'Nigeria',
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
        ]);
    }

    protected function createParent(User $user, array $data)
    {
        return ParentModel::create([
            'user_id' => $user->id,
            'relationship' => $data['relationship'],
            'occupation' => $data['occupation'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? 'Nigeria',
            'secondary_phone' => $data['secondary_phone'] ?? null,
            'preferred_contact_method' => $data['preferred_contact_method'] ?? 'email',
            'receives_weekly_report' => $data['receives_weekly_report'] ?? true,
        ]);
    }

    protected function createInstructor(User $user, array $data)
    {
        return Instructor::create([
            'user_id' => $user->id,
            'qualification' => $data['qualification'],
            'specialization' => $data['specialization'] ?? null,
            'years_of_experience' => $data['years_of_experience'] ?? 0,
            'linkedin_url' => $data['linkedin_url'] ?? null,
            'bio' => $data['bio'] ?? null,
        ]);
    }

    public function success()
    {
        return Inertia::render('Register/Success', [
            'email' => Auth::user()->email ?? null,
        ]);
    }
}
```

## 6. Create Verify Email Vue Component

```vue
<!-- resources/js/Pages/Auth/VerifyEmail.vue -->
<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
  status: String,
});

const isResending = ref(false);
const message = ref(props.status);

const resendVerification = () => {
  isResending.value = true;
  message.value = '';
  
  router.post('/email/verification-notification', {}, {
    preserveScroll: true,
    onSuccess: () => {
      message.value = 'Verification link sent! Please check your email.';
      isResending.value = false;
    },
    onError: () => {
      message.value = 'Failed to send verification email. Please try again.';
      isResending.value = false;
    }
  });
};
</script>

<template>
  <div class="min-h-screen bg-linear-to-b from-gray-50 to-white flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
      <!-- Header -->
      <div class="text-center mb-8">
        <Link href="/" class="inline-flex items-center space-x-3 group mb-6">
          <div class="w-12 h-12 bg-linear-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center transform group-hover:scale-110 transition-all duration-300 shadow-lg">
            <span class="text-2xl font-bold text-white">E</span>
          </div>
          <span class="text-2xl font-bold bg-linear-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
            EduLink
          </span>
        </Link>
      </div>

      <!-- Verification Card -->
      <div class="bg-white rounded-2xl shadow-xl p-12 text-center animate-fade-in-up">
        <!-- Email Icon -->
        <div class="mb-6">
          <div class="mx-auto w-20 h-20 bg-linear-to-r from-emerald-500 to-teal-600 rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </div>
        </div>

        <h2 class="text-3xl font-bold text-gray-900 mb-4">Verify Your Email</h2>
        
        <p class="text-gray-600 mb-6">
          Thanks for signing up! Before getting started, please verify your email address by clicking on the link we just emailed to you.
        </p>

        <p class="text-gray-600 mb-8">
          If you didn't receive the email, we'll gladly send you another.
        </p>

        <!-- Success Message -->
        <div v-if="message" class="mb-6 p-4 rounded-lg" :class="message.includes('sent') ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'">
          {{ message }}
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-6">
          <button
            @click="resendVerification"
            :disabled="isResending"
            class="inline-flex items-center justify-center px-8 py-3 bg-linear-to-r from-emerald-500 to-teal-600 text-white font-bold rounded-lg hover:from-emerald-600 hover:to-teal-700 transform hover:scale-105 transition-all duration-200 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="isResending" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isResending ? 'Sending...' : 'Resend Verification Email' }}
          </button>
          
          <Link
            href="/logout"
            method="post"
            as="button"
            class="inline-flex items-center justify-center px-8 py-3 border-2 border-gray-300 text-gray-700 font-bold rounded-lg hover:bg-gray-50 transition-all duration-200"
          >
            Logout
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.6s ease-out;
}
</style>
```

## 7. Update Routes

```php
<?php
// routes/web.php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Homepage\HomeController;

Route::controller(HomeController::class)->group(function () {
   Route::get('/', 'index')->name('home');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'index')->name('register');
    Route::post('/register', 'save')->name('register.save');
    Route::get('/register/success', 'success')->name('register.success');
});

// Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'notice'])
        ->name('verification.notice');
    
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});
```

## 8. Create Email Template (Optional - for better styling)

```blade
<!-- resources/views/emails/verify-email.blade.php -->
@component('mail::message')
# Verify Your Email Address

Hello {{ $user->first_name }}!

Thank you for registering as a {{ ucfirst($user->role) }} with **EduLink**.

Please click the button below to verify your email address and activate your account.

@component('mail::button', ['url' => $verificationUrl, 'color' => 'success'])
Verify Email Address
@endcomponent

This verification link will expire in **60 minutes**.

If you did not create an account, no further action is required.

Thanks,<br>
The {{ config('app.name') }} Team

---

**Having trouble?** Copy and paste this URL into your browser:
{{ $verificationUrl }}
@endcomponent
```

## 9. Protect Routes with Verification Middleware

Update your Filament panel configurations to require email verification:

```php
<?php
// app/Providers/Filament/StudentPanelProvider.php (and similar for other panels)

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configurations
        ->authMiddleware([
            Authenticate::class,
            \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class, // Add this
        ]);
}
```

## 10. Update .env file

Make sure your mail configuration is set up:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@edulink.ng
MAIL_FROM_NAME="${APP_NAME}"
```

## 11. Test the Implementation

Run these commands:

```bash
# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Run migrations if needed
php artisan migrate

# Queue worker (if using queues)
php artisan queue:work
```

## Usage Flow:

1. User registers → Account created with `email_verified_at = null`
2. Verification email sent automatically
3. User redirected to `/email/verify` page
4. User clicks link in email → Email verified
5. User redirected to appropriate dashboard based on role
6. User can resend verification email if needed

This implementation provides a complete email verification system with:
- ✅ Automatic verification email on registration
- ✅ Custom verification notification
- ✅ Beautiful verification UI
- ✅ Resend verification functionality
- ✅ Role-based redirects after verification
- ✅ Signed URLs for security
- ✅ Rate limiting to prevent abuse
- ✅ Queue support for better performance