@component('mail::message')
New Class Scheduled 

Hi {{ $user->first_name }},

A new class has been scheduled for you.

@component('mail::panel')
Course: {{ $classSession->course->title }}

Title: {{ $classSession->title }}

Date: {{ $classSession->scheduled_at->format('l, F j, Y') }}

Time: {{ $classSession->scheduled_at->format('g:i A') }}
@endcomponent

@if($classSession->description)
About this class:
{{ $classSession->description }}
@endif

@if($classSession->google_meet_link)
@component('mail::button', ['url' => $classSession->google_meet_link])
Join Google Meet
@endcomponent
@endif

Mark your calendar and see you in class!

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent