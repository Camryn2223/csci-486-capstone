@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;">Application Templates - {{ $organization->name }}</h1>
        @can('create', [App\Models\ApplicationTemplate::class, $organization])
            <a href="{{ route('organizations.application-templates.create', $organization) }}" class="btn">+ Create Application Template</a>
        @endcan
    </div>

    @forelse ($templates as $template)
        <div class="card entry-box">
            <div class="entry-top">
                <strong style="font-size: 18px;">{{ $template->name }}</strong>
                <div>
                    <a href="{{ route('organizations.application-templates.show', [$organization, $template]) }}" class="btn btn-sm">Preview</a>
                    @can('update', $template)
                        <a href="{{ route('organizations.application-templates.edit', [$organization, $template]) }}" class="btn btn-sm" style="background: #3a245a; margin-left: 5px;">Edit Fields</a>
                    @endcan
                    @can('delete', $template)
                        <form method="POST" action="{{ route('organizations.application-templates.destroy', [$organization, $template]) }}" style="display:inline; margin-left: 5px;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this template?')">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
            <p style="margin: 5px 0 0 0; color: #bdbdbd;">
                {{ $template->fields_count }} field(s) &bull; {{ $template->applications_count }} application(s) &bull; Created by {{ $template->creator->name }}
            </p>
        </div>
    @empty
        <div class="card"><p>No templates yet.</p></div>
    @endforelse

    <div style="margin-top: 20px; display: flex; gap: 10px;">
        <a href="{{ route('organizations.show', $organization) }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45;">Back to Organization</a>
        <a href="{{ route('organizations.job-positions.index', $organization) }}" class="btn" style="background: #24282d; border: 1px solid #3a3f45;">View Job Positions</a>
    </div>
</div>
@endsection