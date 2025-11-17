@component('mail::message')
Your Grade Has Been Published 

Hi {{ $grade->submission->student->user->first_name }},

Your instructor has graded your assignment!

@component('mail::panel')
Assignment: {{ $grade->submission->assignment->title }}

Your Score: {{ $grade->score }} / {{ $grade->max_score }}

Percentage: {{ $grade->percentage }}%

Grade: {{ $grade->letter_grade }}
@endcomponent

@if($grade->feedback)
Instructor Feedback:

{{ strip_tags($grade->feedback) }}
@endif

@component('mail::button', ['url' => url('/student/grades/' . $grade->id)])
View Detailed Grade
@endcomponent

@if($grade->percentage >= 90)
Excellent work! Keep it up!
@elseif($grade->percentage >= 80)
 Great job!
@elseif($grade->percentage >= 70)
Good effort!
@elseif($grade->percentage >= 60)
You can do better next time!
@else
Don't give up! Consider scheduling a tutoring session.
@endif

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent