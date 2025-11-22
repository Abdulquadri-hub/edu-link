<?php

namespace App\Http\Controllers\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Mail\WelcomeParentMail;
use App\Mail\WelcomeStudentMail;
use App\Mail\WelcomeInstructorMail;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerificationController extends Controller
{
    public function notice()
    {
        return Inertia::render('Auth/VerifyEmail', [
            'status' => session('status'),
        ]);
    }

    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectBasedOnRole($request->user());
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));

            // Send welcome email
            $this->sendWelcomeEmail($request->user());
        }

        return $this->redirectBasedOnRole($request->user())
            ->with('status', 'email-verified');
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectBasedOnRole($request->user());
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    protected function redirectBasedOnRole($user)
    {
        return match($user->user_type) {
            'admin' => redirect()->intended('/admin'),
            'instructor' => redirect()->intended('/instructor'),
            'student' => redirect()->intended('/student'),
            'parent' => redirect()->intended('/parent'),
            default => redirect()->intended('/'),
        };
    }

    private function sendWelcomeEmail($user)
    {
        $role = $user->user_type;
        $emailClass = match($role) {
            'student' => WelcomeStudentMail::class,
            'parent' => WelcomeParentMail::class,
            'instructor' => WelcomeInstructorMail::class,
        };

        Mail::to($user->email)->send(new $emailClass($user));
    }
}