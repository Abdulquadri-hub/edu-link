@component('mail::message')
Welcome to EduLink, {{ $user->first_name }}! 

We're excited to have you join our learning community!

Your account has been successfully created and you can now:

- Browse and enroll in courses
- Attend online classes via Google Meet
- Submit assignments and track your grades
- View your progress dashboard

@component('mail::button', ['url' => url('/student/dashboard')])
Go to Dashboard
@endcomponent

{{-- **Your Login Details:**
- Email: {{ $user->email }}
- Username: {{ $user->username }} --}}

If you have any questions, feel free to contact our support team.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent