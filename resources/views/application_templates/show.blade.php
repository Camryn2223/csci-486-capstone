@extends('layouts.app')

@section('content')
    <h1>Template Preview: {{ $applicationTemplate->name }}</h1>
    <p>Created by {{ $applicationTemplate->creator->name }}</p>

    @forelse ($applicationTemplate->fields as $field)
        <hr>
        <p>
            <strong>{{ $field->label }}</strong>
            [{{ $field->type }}]
            {{ $field->required ? '(required)' : '(optional)' }}
        </p>
        @if ($field->hasOptions())
            <p>Options: {{ implode(', ', $field->options ?? []) }}</p>
        @endif
    @empty
        <p>This template has no fields yet.</p>
    @endforelse

    @can('update', $applicationTemplate)
        <br><a href="{{ route('organizations.application-templates.edit', [$organization, $applicationTemplate]) }}">Edit Fields</a>
    @endcan

    <br><a href="{{ route('organizations.application-templates.index', $organization) }}">Back</a>
@endsection