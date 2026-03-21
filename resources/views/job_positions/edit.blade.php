@extends('layouts.app')

@section('content')
    <h1>Edit Job Position</h1>

    <form method="POST" action="{{ route('organizations.job-positions.update', [$organization, $jobPosition]) }}">
        @csrf
        @method('PUT')
        
        <label>Title<br>
            <input type="text" name="title" value="{{ old('title', $jobPosition->title) }}" required>
        </label>
        <br><br>

        <label>Application Template<br>
            <select name="template_id" required>
                @foreach ($templates as $template)
                    <option value="{{ $template->id }}" {{ old('template_id', $jobPosition->template_id) == $template->id ? 'selected' : '' }}>
                        {{ $template->name }} ({{ $template->fields_count }} fields)
                    </option>
                @endforeach
            </select>
        </label>
        <br><br>

        <label>Description<br>
            <textarea name="description" rows="4" cols="50" required>{{ old('description', $jobPosition->description) }}</textarea>
        </label>
        <br><br>
        <label>Requirements<br>
            <textarea name="requirements" rows="4" cols="50" required>{{ old('requirements', $jobPosition->requirements) }}</textarea>
        </label>
        <br><br>
        <label>Status<br>
            <select name="status">
                <option value="open" {{ old('status', $jobPosition->status) === 'open' ? 'selected' : '' }}>Open</option>
                <option value="closed" {{ old('status', $jobPosition->status) === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </label>
        <br><br>
        <button type="submit">Save</button>
    </form>

    <br><a href="{{ route('organizations.job-positions.show', [$organization, $jobPosition]) }}">Cancel</a>
@endsection