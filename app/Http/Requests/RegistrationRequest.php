<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $baseRules = [
            // Step 1: Role
            'role' => ['required', 'string', 'in:student,parent,instructor'],
            
            // Step 2: Personal Information
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username', 'regex:/^[a-zA-Z0-9_]+$/'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()],
            'agrees_to_terms' => ['required', 'accepted'],
        ];

        // Role-specific validation
        $roleSpecificRules = match($this->input('role')) {
            'student' => $this->studentRules(),
            'parent' => $this->parentRules(),
            'instructor' => $this->instructorRules(),
            default => []
        };

        return array_merge($baseRules, $roleSpecificRules);
    }

    /**
     * Student-specific validation rules
     */
    private function studentRules(): array
    {
        return [
            'date_of_birth' => ['required', 'date', 'before:today', 'after:' . now()->subYears(100)->format('Y-m-d')],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Parent-specific validation rules
     */
    private function parentRules(): array
    {
        return [
            'occupation' => ['nullable', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'in:father,mother,guardian,other'],
            'secondary_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'preferred_contact_method' => ['nullable', 'string', 'in:email,phone,sms'],
            'receives_weekly_report' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Instructor-specific validation rules
     */
    private function instructorRules(): array
    {
        return [
            'qualification' => ['required', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:50'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'role.required' => 'Please select a role to continue.',
            'role.in' => 'Invalid role selected.',
            'email.unique' => 'This email address is already registered.',
            'username.unique' => 'This username is already taken.',
            'username.regex' => 'Username can only contain letters, numbers, and underscores.',
            'password.confirmed' => 'Password confirmation does not match.',
            'agrees_to_terms.accepted' => 'You must agree to the Terms of Service and Privacy Policy.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'date_of_birth.after' => 'Invalid date of birth.',
            'gender.in' => 'Please select a valid gender.',
            'relationship.required' => 'Please select your relationship to the student.',
            'relationship.in' => 'Please select a valid relationship.',
            'qualification.required' => 'Qualification is required for instructors.',
            'years_of_experience.min' => 'Years of experience cannot be negative.',
            'years_of_experience.max' => 'Years of experience seems too high.',
            'linkedin_url.url' => 'Please enter a valid LinkedIn URL.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'date_of_birth' => 'date of birth',
            'emergency_contact_name' => 'emergency contact name',
            'emergency_contact_phone' => 'emergency contact phone',
            'secondary_phone' => 'secondary phone',
            'preferred_contact_method' => 'preferred contact method',
            'receives_weekly_report' => 'weekly report preference',
            'years_of_experience' => 'years of experience',
            'linkedin_url' => 'LinkedIn URL',
        ];
    }
}