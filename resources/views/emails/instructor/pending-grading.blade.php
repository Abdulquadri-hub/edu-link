@component('mail::message')
Pending Submissions to Grade

Hi {{ $instructor->user->first_name }},

You have **{{ $pendingCount }}** student submissions waiting to be graded.

Students are eagerly awaiting your feedback!

@component('mail::button', ['url' => url('/instructor/submissions')])
View Pending Submissions
@endcomponent

**Reminder:** Timely feedback helps students learn and improve.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent