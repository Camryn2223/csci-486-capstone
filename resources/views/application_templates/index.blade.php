@extends('layouts.app')

@section('content')
    <h1>Application Templates - {{ $organization->name }}</h1>

    @can('create', [App\Models\ApplicationTemplate::class, $organization])
        <a href="{{ route('organizations.application-templates.create', $organization) }}">+ Create Template</a>
    @endcan

    @forelse ($templates as $template)
        <hr>
        <p>
            <strong>{{ $template->name }}</strong>
            - {{ $template->fields_count }} field(s), {{ $template->applications_count }} application(s)
            - Created by {{ $template->creator->name }}
        </p>
        <a href="{{ route('organizations.application-templates.show', [$organization, $template]) }}">Preview</a>
        @can('update', $template)
            | <a href="{{ route('organizations.application-templates.edit', [$organization, $template]) }}">Edit Fields</a>
        @endcan
        @can('delete', $template)
            |
            <form method="POST" action="{{ route('organizations.application-templates.destroy', [$organization, $template]) }}" style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Delete this template?')">Delete</button>
            </form>
        @endcan
    @empty
        <p>No templates yet.</p>
    @endforelse

    <br><a href="{{ route('organizations.show', $organization) }}">Back</a>
@endsection