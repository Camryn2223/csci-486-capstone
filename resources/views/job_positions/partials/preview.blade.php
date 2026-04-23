<div class="card">
    <h1 class="mt-0">Apply for: <span id="preview-title">{{ $jobPosition?->title ?? 'Untitled Position' }}</span></h1>
    <p class="text-primary fs-18 mb-20"><strong>Organization:</strong> {{ $organization->name }}</p>

    <div id="preview-desc" class="text-light rich-text-content">{!! clean($jobPosition?->description ?? 'No description provided.') !!}</div>

    <h3 class="mt-25">Requirements:</h3>
    <div id="preview-reqs" class="text-light rich-text-content">{!! clean($jobPosition?->requirements ?? 'No requirements provided.') !!}</div>
</div>

<div class="card">
    <div id="preview-form-container">
        @if(isset($isBuilder) && $isBuilder)
            @foreach ($templates as $t)
                <div id="template-preview-{{ $t->id }}" style="display: {{ (old('template_id', $jobPosition?->template_id ?? '') == $t->id) ? 'block' : 'none' }};" class="template-preview-block">
                    @include('applications.partials.form-fields', ['template' => $t, 'isPreview' => true])
                </div>
            @endforeach
            <div id="template-preview-none" class="template-preview-block" style="display: {{ old('template_id', $jobPosition?->template_id ?? '') ? 'none' : 'block' }};">
                <p class="text-muted text-italic">Select an application template to preview the form.</p>
            </div>
        @else
            <form id="application-form" method="POST" action="{{ route('applications.store', [$organization, $jobPosition]) }}" enctype="multipart/form-data">
                @csrf
                @include('applications.partials.form-fields', ['template' => $jobPosition->template, 'isPreview' => false])
            </form>
        @endif
    </div>
</div>