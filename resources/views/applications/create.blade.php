@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex mb-25" style="padding: 15px 20px;">
        <h1 class="m-0 fs-20">Application Form</h1>
        <a href="{{ route('organizations.job-positions.index', $organization) }}" class="btn btn-outline">Back to Job Positions</a>
    </div>

    @include('job_positions.partials.preview', ['jobPosition' => $jobPosition, 'organization' => $organization, 'isBuilder' => false])
</div>
@endsection