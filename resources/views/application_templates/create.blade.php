@extends('layouts.app')

@section('content')
    <h1>Create Application Template - {{ $organization->name }}</h1>

    <form method="POST" action="{{ route('organizations.application-templates.store', $organization) }}">
        @csrf
        <label>Template Name<br>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </label>
        <br><br>
        <button type="submit">Create</button>
    </form>

    <br><a href="{{ route('organizations.application-templates.index', $organization) }}">Cancel</a>
@endsection