<x-mail::message>
# Application Update

Dear {{ $application->applicant_name }},

Thank you for your interest in the **{{ $application->jobPosition->title }}** position at **{{ $application->jobPosition->organization->name }}**.

After careful review, we have decided not to move forward with your application at this time.

@if($reason)
**Additional information from the hiring team:**

{{ $reason }}
@endif

We appreciate the time you invested in applying and wish you the best in your job search.

Regards,<br>
{{ $application->jobPosition->organization->name }}
</x-mail::message>