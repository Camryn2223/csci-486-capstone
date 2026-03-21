<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply - {{ $jobPosition->title }}</title>
</head>
<body>

<h1>Apply for: {{ $jobPosition->title }}</h1>
<p><strong>Organization:</strong> {{ $organization->name }}</p>
<p>{{ $jobPosition->description }}</p>
<p><strong>Requirements:</strong> {{ $jobPosition->requirements }}</p>

@if ($errors->any())
    <ul style="color:red">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ route('applications.store', [$organization, $jobPosition]) }}" enctype="multipart/form-data">
    @csrf

    <h2>Your Information</h2>

    <label>Full Name<br>
        <input type="text" name="applicant_name" value="{{ old('applicant_name') }}" required>
    </label>
    <br><br>
    <label>Email Address<br>
        <input type="email" name="applicant_email" value="{{ old('applicant_email') }}" required>
    </label>
    <br><br>
    <label>Phone Number (optional)<br>
        <input type="text" name="applicant_phone" value="{{ old('applicant_phone') }}">
    </label>

    @if ($jobPosition->template)
        <input type="hidden" name="template_id" value="{{ $jobPosition->template->id }}">

        @if ($jobPosition->template->fields->isNotEmpty())
            <h2>Application Questions</h2>
            @foreach ($jobPosition->template->fields as $field)
                <div>
                    <label>
                        {{ $field->label }}
                        {{ $field->required ? '*' : '' }}
                    </label>
                    <br>

                    @if ($field->type === 'text')
                        <input type="text" name="answers[{{ $field->id }}]"
                               value="{{ old("answers.{$field->id}") }}"
                               {{ $field->required ? 'required' : '' }}>

                    @elseif ($field->type === 'textarea')
                        <textarea name="answers[{{ $field->id }}]" rows="4" cols="50"
                                  {{ $field->required ? 'required' : '' }}>{{ old("answers.{$field->id}") }}</textarea>

                    @elseif ($field->type === 'date')
                        <input type="date" name="answers[{{ $field->id }}]"
                               value="{{ old("answers.{$field->id}") }}"
                               {{ $field->required ? 'required' : '' }}>

                    @elseif ($field->type === 'select')
                        <select name="answers[{{ $field->id }}]" {{ $field->required ? 'required' : '' }}>
                            <option value="">-- Select --</option>
                            @foreach ($field->options ?? [] as $option)
                                <option value="{{ $option }}"
                                    {{ old("answers.{$field->id}") === $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>

                    @elseif ($field->type === 'radio')
                        @foreach ($field->options ?? [] as $option)
                            <label>
                                <input type="radio" name="answers[{{ $field->id }}]" value="{{ $option }}"
                                    {{ old("answers.{$field->id}") === $option ? 'checked' : '' }}
                                    {{ $field->required ? 'required' : '' }}>
                                {{ $option }}
                            </label>
                        @endforeach

                    @elseif ($field->type === 'checkbox')
                        @foreach ($field->options ?? [] as $option)
                            <label>
                                <input type="checkbox" name="answers[{{ $field->id }}][]" value="{{ $option }}">
                                {{ $option }}
                            </label>
                        @endforeach

                    @elseif ($field->type === 'file')
                        <input type="file" name="answers[{{ $field->id }}]"
                               {{ $field->required ? 'required' : '' }}>
                    @endif
                    <br><br>
                </div>
            @endforeach
        @endif
    @endif

    <h2>Upload Documents</h2>
    <p>You may upload a resume or other supporting documents (PDF, DOC, DOCX, JPG, PNG - max 10MB each).</p>
    <input type="file" name="document">
    <br><br>

    <button type="submit">Submit Application</button>
</form>

</body>
</html>