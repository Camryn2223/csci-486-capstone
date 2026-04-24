@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card card-header-flex">
        <h1 class="m-0">All Application Templates</h1>
        <div class="flex-gap-10">
            @can('create', [App\Models\ApplicationTemplate::class, $organization])
                <a href="{{ route('organizations.application-templates.create', $organization) }}" class="btn">+ Create Application Template</a>
            @endcan
            <a href="{{ route('organizations.job-positions.index', $organization) }}" class="btn btn-outline">View Job Positions</a>
            <a href="{{ route('organizations.show', $organization) }}" class="btn btn-outline">Back to Organization</a>
        </div>
    </div>

    @forelse ($templates as $template)
        <div class="card entry-box">
            <div class="entry-top">
                <strong class="fs-18">{{ $template->name }}</strong>
                <div class="flex-gap-5">
                    <a href="{{ route('organizations.application-templates.show', [$organization, $template]) }}" class="btn btn-sm">View</a>
                    @can('update', $template)
                        <a href="{{ route('organizations.application-templates.edit', [$organization, $template]) }}" class="btn btn-sm btn-slate">Edit</a>
                    @endcan
                    @can('delete', $template)
                        <form method="POST" action="{{ route('organizations.application-templates.destroy', [$organization, $template]) }}" class="d-inline m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this template?')">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
            <p class="m-0 mt-5 text-muted">
                {{ $template->fields_count }} field(s) &bull; Created by {{ $template->creator->name }}
            </p>
        </div>
    @empty
        <div class="card"><p>No templates yet.</p></div>
    @endforelse
</div>
@endsection