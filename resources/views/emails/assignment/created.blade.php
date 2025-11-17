Hi {{ $user->first_name }},

Your instructor has posted a new assignment.

@component('mail::panel')
Course: {{ $assignment->course->title }}

Assignment: {{ $assignment->title }}

Type: {{ ucfirst($assignment->type) }}

Due Date: {{ $assignment->due_at->format('l, F j, Y \a\t g:i A') }}

Maximum Score: {{ $assignment->max_score }} points
@endcomponent

Description:
{{ strip_tags($assignment->description) }}

@component('mail::button', ['url' => url('/student/assignments/' . $assignment->id)])
View Assignment
@endcomponent

@if($assignment->allows_late_submission)
Late submissions are allowed but will incur a {{ $assignment->late_penalty_percentage }}% penalty.
@else
Late submissions will NOT be accepted.
@endif

Good luck!

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent
