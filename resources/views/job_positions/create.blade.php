@extends('layouts.app')

@section('content')
    <h1>Create Job Position - {{ $organization->name }}</h1>

    <form method="POST" action="{{ route('organizations.job-positions.store', $organization) }}">
        @csrf
        <label>Title<br>
            <input type="text" name="title" value="{{ old('title') }}" required>
        </label>
        <br><br>

        <label>Application Template<br>
            <select name="template_id" required>
                <option value="">-- Select Template --</option>
                @foreach ($templates as $template)
                    <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                        {{ $template->name }} ({{ $template->fields_count }} fields)
                    </option>
                @endforeach
            </select>
        </label>
        <br><br>

        <label>Description<br>
            <textarea name="description" rows="4" cols="50" required>{{ old('description') }}</textarea>
        </label>
        <br><br>
        <label>Requirements<br>
            <textarea name="requirements" rows="4" cols="50" required>{{ old('requirements') }}</textarea>
        </label>
        <br><br>
        <label>Status<br>
            <select name="status">
                <option value="open" {{ old('status') === 'open' ? 'selected' : '' }}>Open</option>
                <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </label>
        <br><br>
        <button type="submit">Create</button>
    </form>

    <br><a href="{{ route('organizations.job-positions.index', $organization) }}">Cancel</a>
@endsection