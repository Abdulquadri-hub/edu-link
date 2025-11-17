@component('mail::message')
Welcome to EduLink, {{ $user->first_name }}!

Thank you for trusting us with your child's education!

As a parent, you can now:

- Monitor your child's academic progress in real-time
- View grades and attendance records
- Receive weekly progress reports
- Communicate with instructors
- Stay informed about upcoming classes

@component('mail::button', ['url' => url('/parent/dashboard')])
Go to Dashboard
@endcomponent

{{-- Your Login Details:
- Email: {{ $user->email }}
- Username: {{ $user->username }} --}}

Next Step: Link your child(ren) to your account to start monitoring their progress.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent
