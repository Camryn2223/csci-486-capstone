@extends('layouts.app')

@section('content')
<div class="container">
    @include('job_positions.partials.preview', ['jobPosition' => $jobPosition, 'organization' => $organization, 'isBuilder' => false])
</div>
@endsection