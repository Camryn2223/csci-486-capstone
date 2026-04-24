<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Application - {{ $application->applicant_name }}</title>
    <style>
        body { 
            font-family: Helvetica, Arial, sans-serif; 
            color: #333; 
            line-height: 1.5; 
            font-size: 14px;
        }
        h1 { 
            font-size: 24px; 
            margin-bottom: 5px; 
            color: #111827;
        }
        h2 { 
            font-size: 18px; 
            margin-top: 30px; 
            border-bottom: 1px solid #e5e7eb; 
            padding-bottom: 5px; 
            color: #374151;
        }
        .meta { 
            font-size: 14px; 
            color: #4b5563; 
            margin-bottom: 25px; 
        }
        .meta p { 
            margin: 4px 0; 
        }
        .qa-block { 
            margin-bottom: 20px; 
            page-break-inside: avoid;
        }
        .question { 
            font-weight: bold; 
            margin-bottom: 6px; 
            color: #1f2937;
        }
        .answer { 
            margin-left: 10px; 
            color: #4b5563;
        }
        .attachment {
            color: #6b7280;
            font-style: italic;
        }
        .interview-block {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .interview-date {
            font-weight: bold;
            font-size: 15px;
            color: #1f2937;
            margin-bottom: 10px;
            border-bottom: 1px dashed #e5e7eb;
            padding-bottom: 5px;
        }
        .note-item {
            margin-bottom: 15px;
        }
        .note-author {
            font-weight: bold;
            color: #374151;
            margin-bottom: 4px;
        }
        .note-content {
            margin-left: 10px;
            padding-left: 10px;
            border-left: 3px solid #e5e7eb;
            color: #4b5563;
        }
        .note-content p {
            margin-top: 0;
            margin-bottom: 8px;
        }
        .note-content p:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <h1>{{ $application->applicant_name }}</h1>
    
    <div class="meta">
        <p><strong>Position:</strong> {{ $application->jobPosition->title }}</p>
        <p><strong>Organization:</strong> {{ $application->jobPosition->organization->name }}</p>
        <p><strong>Email:</strong> {{ str_contains($application->applicant_email, 'no-email-') ? 'Not Provided' : $application->applicant_email }}</p>
        <p><strong>Phone:</strong> {{ $application->applicant_phone ?? 'Not Provided' }}</p>
        <p><strong>Submitted:</strong> {{ $application->created_at->format('F j, Y g:i A') }}</p>
        <p><strong>Current Status:</strong> {{ str_replace('_', ' ', \Illuminate\Support\Str::title($application->status)) }}</p>
    </div>

    <h2>Application Answers</h2>
    
    @foreach($application->answers as $answer)
        <div class="qa-block">
            <div class="question">{{ $answer->field->label }}</div>
            <div class="answer">
                @if($answer->document)
                    <span class="attachment">[Attached Document: {{ $answer->document->filename }}]</span>
                @elseif($answer->field->type === 'rich_text')
                    {!! strip_tags($answer->value ?? 'No answer provided', '<p><br><ul><ol><li><strong><em><b><i>') !!}
                @else
                    {!! nl2br(e($answer->value ?? 'No answer provided')) !!}
                @endif
            </div>
        </div>
    @endforeach

    @if($application->interviews && $application->interviews->isNotEmpty())
        @php
            $interviewsWithNotes = $application->interviews->filter(function($interview) {
                return $interview->interviewers->contains(function($interviewer) {
                    return filled($interviewer->pivot->notes);
                });
            });
        @endphp

        @if($interviewsWithNotes->isNotEmpty())
            <h2>Interview Notes</h2>

            @foreach($interviewsWithNotes as $interview)
                <div class="interview-block">
                    <div class="interview-date">
                        Interview on {{ $interview->scheduled_at->format('F j, Y \a\t g:i A') }}
                    </div>
                    
                    @foreach($interview->interviewers as $interviewer)
                        @if(filled($interviewer->pivot->notes))
                            <div class="note-item">
                                <div class="note-author">{{ $interviewer->name }}:</div>
                                <div class="note-content">
                                    {!! strip_tags($interviewer->pivot->notes, '<p><br><ul><ol><li><strong><em><b><i>') !!}
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        @endif
    @endif
</body>
</html>