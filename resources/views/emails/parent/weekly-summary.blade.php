@component('mail::message')
Weekly Progress Report 

Hi {{ $parent->user->first_name }},

Here's a summary of your children's progress this week.

@foreach($summary as $child)

{{ $child['name'] }}

Active Courses: {{ $child['courses'] }}
Average Grade: {{ $child['average_grade'] ? round($child['average_grade'], 1) . '%' : 'N/A' }}
Attendance Rate: {{ $child['attendance_rate'] ? round($child['attendance_rate'], 1) . '%' : 'N/A' }}

@if($child['average_grade'] && $child['average_grade'] < 60)
 Alert: {{ $child['name'] }}'s average grade is below 60%. Consider reaching out to their instructors.
@endif

@if($child['attendance_rate'] && $child['attendance_rate'] < 75)
 Alert: {{ $child['name'] }}'s attendance is below 75%. Please ensure they attend classes regularly.
@endif

@endforeach

---

@component('mail::button', ['url' => url('/parent/dashboard')])
View Full Dashboard
@endcomponent

Thank you for being an involved parent!

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent