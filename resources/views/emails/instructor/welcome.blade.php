@component('mail::message')
Welcome to EduLink Instructor Portal, {{ $user->first_name }}!

We're thrilled to have you join our teaching team!

Your instructor account has been created. Here are your login credentials:

Login Details:
- Email: {{ $user->email }}
- Temporary Password: `{{ $temporaryPassword }}`

@component('mail::panel')
 Important: Please change your password immediately after logging in for security reasons.
@endcomponent

As an instructor, you can:

- Manage your courses and students
- Schedule and conduct online classes
- Create and grade assignments
- Upload course materials
- Track student progress

@component('mail::button', ['url' => url('/instructor/dashboard')])
Login to Instructor Portal
@endcomponent

If you need any assistance, please contact the admin team.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent
