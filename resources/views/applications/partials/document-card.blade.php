<div class="entry-box card-header-flex mt-10">
    <div>
        <span class="font-mono fs-16 d-block">{{ $document->filename }} <span class="text-muted fs-13">({{ $document->mimetype }})</span></span>
        @if($document->uploaded_by && $document->uploader)
            <span class="text-primary fs-13 mt-5 d-block">Uploaded by {{ $document->uploader->name }}</span>
        @endif
    </div>
    
    <div class="flex-gap-10 items-center">
        <a href="{{ route('documents.show', $document) }}" class="btn btn-sm" target="_blank">View</a>
        <a href="{{ route('documents.show', ['document' => $document, 'download' => 1]) }}" class="btn btn-sm btn-slate">Download</a>
        
        @can('delete', $document)
            <form method="POST" action="{{ route('documents.destroy', $document) }}" class="m-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this document?')">Delete</button>
            </form>
        @endcan
    </div>
</div>