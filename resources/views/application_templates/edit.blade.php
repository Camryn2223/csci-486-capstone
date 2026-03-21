@extends('layouts.app')

@section('content')
    <h1>Edit Template: {{ $applicationTemplate->name }}</h1>

    {{-- Rename template --}}
    <h2>Rename</h2>
    <form method="POST" action="{{ route('organizations.application-templates.update', [$organization, $applicationTemplate]) }}">
        @csrf
        @method('PUT')
        <input type="text" name="name" value="{{ old('name', $applicationTemplate->name) }}" required>
        <button type="submit">Save Name</button>
    </form>

    <hr>

    {{-- Existing fields --}}
    <h2>Fields</h2>
    @forelse ($applicationTemplate->fields as $field)
        <p>
            <strong>{{ $field->label }}</strong>
            [{{ $field->type }}]
            {{ $field->required ? '(required)' : '' }}
            - order {{ $field->order }}
        </p>

        {{-- Edit field inline --}}
        <form method="POST" action="{{ route('organizations.application-templates.fields.update', [$organization, $applicationTemplate, $field]) }}">
            @csrf
            @method('PATCH')

            @php
                // Handle preserving state if validation fails
                $currentType = old('type') !== null && old('type') !== $field->type ? old('type') : $field->type;
                $showOptions = in_array($currentType, ['select', 'checkbox', 'radio']);
                
                // If validation failed, options will be an array we merged in the controller
                $oldOptions = old('options');
                if (is_array($oldOptions)) {
                    $currentOptions = implode(', ', $oldOptions);
                } else {
                    $currentOptions = implode(', ', $field->options ?? []);
                }
            @endphp

            <input type="text" name="label" value="{{ old('label', $field->label) }}" required>
            
            <select name="type" onchange="toggleOptions(this)">
                @foreach (['text','textarea','select','checkbox','radio','file','date'] as $type)
                    <option value="{{ $type }}" {{ $currentType === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
            
            <label>
                <input type="checkbox" name="required" value="1" {{ old('required', $field->required) ? 'checked' : '' }}> Required
            </label>
            
            <div class="options-container" style="display: {{ $showOptions ? 'block' : 'none' }}; margin-top: 5px; margin-bottom: 5px;">
                <label>Options (comma-separated)<br>
                    <input type="text" name="options" value="{{ $currentOptions }}" style="width: 100%; max-width: 400px;">
                </label>
            </div>
            
            <br>
            <button type="submit">Update</button>
        </form>

        <form method="POST" action="{{ route('organizations.application-templates.fields.destroy', [$organization, $applicationTemplate, $field]) }}" style="display:inline">
            @csrf
            @method('DELETE')
            <button type="submit" onclick="return confirm('Delete this field?')">Delete Field</button>
        </form>
        <hr>
    @empty
        <p>No fields yet.</p>
    @endforelse

    {{-- Add new field --}}
    <h2>Add Field</h2>
    <form method="POST" action="{{ route('organizations.application-templates.fields.store', [$organization, $applicationTemplate]) }}">
        @csrf

        @php
            $addType = old('type', 'text');
            $addShowOptions = in_array($addType, ['select', 'checkbox', 'radio']);
            $addOptions = is_array(old('options')) ? implode(', ', old('options')) : old('options');
        @endphp

        <label>Label<br>
            <input type="text" name="label" value="{{ old('label') }}" required>
        </label>
        <br><br>
        
        <label>Type<br>
            <select name="type" onchange="toggleOptions(this)">
                @foreach (['text','textarea','select','checkbox','radio','file','date'] as $type)
                    <option value="{{ $type }}" {{ $addType === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </label>
        <br><br>

        <div class="options-container" style="display: {{ $addShowOptions ? 'block' : 'none' }}; margin-bottom: 15px;">
            <label>Options (comma-separated)<br>
                <input type="text" name="options" value="{{ $addOptions }}" style="width: 100%; max-width: 400px;">
            </label>
        </div>
        
        <label><input type="checkbox" name="required" value="1" {{ old('required') ? 'checked' : '' }}> Required</label>
        <br><br>
        <button type="submit">Add Field</button>
    </form>

    <br><a href="{{ route('organizations.application-templates.index', $organization) }}">Back</a>

    <script>
        function toggleOptions(selectElement) {
            const optionsContainer = selectElement.closest('form').querySelector('.options-container');
            if (['select', 'checkbox', 'radio'].includes(selectElement.value)) {
                optionsContainer.style.display = 'block';
            } else {
                optionsContainer.style.display = 'none';
            }
        }
    </script>
@endsection