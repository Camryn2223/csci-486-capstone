<x-mail::message>
# Application Shared: {{ $application->applicant_name }}

You have been sent an application for the **{{ $application->jobPosition->title }}** position at **{{ $application->jobPosition->organization->name }}**.

@if($customMessage)
**Message:**

{{ $customMessage }}
@endif

A PDF copy of the application is attached to this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>