@component('mail::message')
Class Reminder: Tomorrow!

Hi {{ $user->first_name }},

This is a friendly reminder that you have a class tomorrow.

@component('mail::panel')
Course: {{ $classSession->course->title }}

Title: {{ $classSession->title }}

Date: {{ $classSession->scheduled_at->format('l, F j, Y') }}

Time: {{ $classSession->scheduled_at->format('g:i A') }}
@endcomponent

@if($classSession->google_meet_link)
@component('mail::button', ['url' => $classSession->google_meet_link])
Join Google Meet
@endcomponent
@endif

Preparation Tips:
{{-- - Review previous materials --}}
- Prepare any questions you have
- Test your internet connection
- Have a pen and notebook ready

See you tomorrow!

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent
