@component('mail::message')
Assignment Due Tomorrow!

Hi {{ $user->first_name }},

This is a reminder that an assignment is due tomorrow.

@component('mail::panel')
Course: {{ $assignment->course->title }}

Assignment: {{ $assignment->title }}

Due: {{ $assignment->due_at->format('l, F j, Y \a\t g:i A') }}

Maximum Score: {{ $assignment->max_score }} points
@endcomponent

@component('mail::button', ['url' => url('/student/assignments/' . $assignment->id)])
Submit Assignment
@endcomponent

Don't wait until the last minute!

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent
