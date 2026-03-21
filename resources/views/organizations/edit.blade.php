@extends('layouts.app')

@section('content')
    <h1>Edit Organization</h1>

    <form method="POST" action="{{ route('organizations.update', $organization) }}">
        @csrf
        @method('PUT')
        <label>Name<br>
            <input type="text" name="name" value="{{ old('name', $organization->name) }}" required>
        </label>
        <br><br>
        <button type="submit">Save</button>
    </form>

    <br><a href="{{ route('organizations.show', $organization) }}">Cancel</a>
@endsection