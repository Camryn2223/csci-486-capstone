<div class="entry-box card-header-flex mt-10">
    <span class="font-mono fs-16">{{ $document->filename }} <span class="text-muted fs-13">({{ $document->mimetype }})</span></span>
    
    <div class="flex-gap-10">
        <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-slate" target="_blank">View</a>
        <a href="{{ route('documents.show', ['document' => $document, 'download' => 1]) }}" class="btn btn-sm btn-purple-dark">Download</a>
        
        @can('delete', $document)
            <form method="POST" action="{{ route('documents.destroy', $document) }}" class="m-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this document?')">Delete</button>
            </form>
        @endcan
    </div>
</div>