@extends('layouts.app')

@section('content')
    <h1>Create Organization</h1>

    <form method="POST" action="{{ route('organizations.store') }}">
        @csrf
        <label>Name<br>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </label>
        <br><br>
        <button type="submit">Create</button>
    </form>

    <br><a href="{{ route('organizations.index') }}">Cancel</a>
@endsection