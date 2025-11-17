<?php

namespace App\Http\Controllers\Auth;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Contracts\Services\ParentServiceInterface;
use App\Contracts\Services\StudentServiceInterface;
use App\Contracts\Services\InstructorServiceInterface;

class RegisterController extends Controller
{
    public function __construct(
        protected StudentServiceInterface $studentService,
        protected parentServiceInterface $parentService,
        protected InstructorServiceInterface $instructorService
    ) {}

    public function index() {
        return Inertia::render('Register');
    }

    public function save(RegistrationRequest $request) {
        try {
            return DB::transaction( function () use ($request) {
                
                $data = $request->validated();
                $role = $data['role'];

                // Create user based on role
                $user = match($role) {
                    'student' => $this->studentService->createStudent($data),
                    'parent' => $this->parentService->createParent($data),
                    'instructor' => $this->instructorService->createInstructor($data),
                    default => throw new \Exception('Invalid role selected')
                };

                // Send welcome email
                // $this->sendWelcomeEmail($user, $role);

                return redirect()->route('register.success')->with([
                    'success' => true,
                    'message' => 'Registration successful! Please check your email to verify your account.',
                    'email' => $data['email']
                ]);
            });

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Registration failed: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function success() {
        return Inertia::render('Register/Success');
    }

}
