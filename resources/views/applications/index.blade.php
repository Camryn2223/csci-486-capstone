@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex">
        <div>
            <h1 class="m-0">Applications - {{ $jobPosition->title }}</h1>
            <p class="text-muted m-0 mt-5">Organization: {{ $organization->name }}</p>
        </div>
        <a href="{{ route('organizations.job-positions.show', [$organization, $jobPosition]) }}" class="btn btn-outline">Back to Position</a>
    </div>

    @forelse ($applications as $application)
        <div class="card entry-box">
            <div class="entry-top">
                <strong class="fs-18">{{ $application->applicant_name }} <span class="text-muted fs-14">({{ $application->applicant_email }})</span></strong>
                <div class="flex-gap-10 items-center">
                    <a href="{{ route('applications.show', $application) }}" class="btn btn-sm">View</a>
                    @can('delete', $application)
                        <form method="POST" action="{{ route('applications.destroy', $application) }}" class="d-inline m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this application?')">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
            <p class="m-0 mt-5 text-muted">
                Status: <strong class="text-light">{{ str_replace('_', ' ', Str::title($application->status)) }}</strong> &bull; 
                Submitted: {{ $application->created_at->format('M j, Y') }}
            </p>
        </div>
    @empty
        <div class="card"><p>No applications yet.</p></div>
    @endforelse
</div>
@endsection