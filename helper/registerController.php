<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Contracts\Services\StudentServiceInterface;
use App\Contracts\Services\ParentServiceInterface;
use App\Contracts\Services\InstructorServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class Register_Controller extends Controller
{
    public function __construct(
        private StudentServiceInterface $studentService,
        private ParentServiceInterface $parentService,
        private InstructorServiceInterface $instructorService
    ) {}

    /**
     * Show registration form
     */
    public function index()
    {
        return Inertia::render('Register');
    }

    /**
     * Handle registration submission
     */
    public function store(RegistrationRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $role = $data['role'];

            // Create user based on role
            $user = match($role) {
                'student' => $this->studentService->createStudent($data),
                'parent' => $this->parentService->createParent($data),
                'instructor' => $this->instructorService->createInstructor($data),
                default => throw new \Exception('Invalid role selected')
            };

            DB::commit();

            // Send welcome email
            $this->sendWelcomeEmail($user, $role);

            // Return success response
            return redirect()->route('register.success')->with([
                'success' => true,
                'message' => 'Registration successful! Please check your email to verify your account.',
                'email' => $data['email']
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Registration failed: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Show registration success page
     */
    public function success()
    {
        return Inertia::render('Register/Success');
    }

    /**
     * Send welcome email based on role
     */
    private function sendWelcomeEmail($user, $role)
    {
        $emailClass = match($role) {
            'student' => \App\Mail\WelcomeStudentMail::class,
            'parent' => \App\Mail\WelcomeParentMail::class,
            'instructor' => \App\Mail\WelcomeInstructorMail::class,
        };

        Mail::to($user->user->email)->send(new $emailClass($user));
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $user = \App\Models\User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'Email not found']);
        }

        if ($user->hasVerifiedEmail()) {
            return back()->with('message', 'Email already verified');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('message', 'Verification email sent!');
    }
}


