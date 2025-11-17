<?php 

namespace App\Services\Auth;

use Illuminate\Support\Facades\DB;

class RegisterService  {

    public function register($role, $registrationData, $student, $parent, $instructor)
    {
        try {
            DB::beginTransaction();

            // Create user based on role
            $user = match($role) {
                'student' => $this->studentService->createStudent($registrationData),
                'parent' => $this->parentService->createParent($registrationData),
                'instructor' => $this->instructorService->createInstructor($registrationData),
                default => throw new \Exception('Invalid role selected')
            };

            DB::commit();

            // Send welcome email
            // $this->sendWelcomeEmail($user, $role);

            // Return success response
            return redirect()->route('register.success')->with([
                'success' => true,
                'message' => 'Registration successful! Please check your email to verify your account.',
                'email' => $registrationData['email']
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Registration failed: ' . $e->getMessage()
            ])->withInput();
        }
    }
}