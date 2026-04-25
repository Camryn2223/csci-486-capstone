<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Document;
use App\Models\TemplateField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Handles uploading, downloading, and deleting of documents attached to
 * applications. Documents are uploaded by anonymous applicants as part of the
 * public submission flow and require no authentication. Downloading and
 * deleting are restricted to authenticated staff with review_applications in
 * the document's organization.
 */
class DocumentController extends Controller
{
    /**
     * Allowed MIME types for uploaded documents.
     */
    private const ALLOWED_MIMETYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
    ];

    /**
     * Maximum upload size in kilobytes (7.5 MB safe limit for 8MB environments).
     */
    private const MAX_SIZE_KB = 7680;

    /**
     * Store an uploaded document and attach it to the given application. No
     * authentication required - called as part of the public application
     * submission flow. Validates file type and size before storing.
     */
    public function store(Request $request, Application $application): RedirectResponse
    {
        $request->validate([
            'document' => [
                'required',
                'file',
                'max:' . self::MAX_SIZE_KB,
                'mimetypes:' . implode(',', self::ALLOWED_MIMETYPES),
            ],
        ]);

        $file     = $request->file('document');
        $filepath = $file->store("documents/{$application->id}", 'local');

        $application->documents()->create([
            'filename' => $file->getClientOriginalName(),
            'filepath' => $filepath,
            'mimetype' => $file->getMimeType(),
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    /**
     * Store a document uploaded by staff for a specific custom file field.
     * No strict validation on size/mime types, just basic file presence, 
     * since the uploader is a trusted internal user.
     */
    public function storeStaff(Request $request, Application $application, TemplateField $templateField): RedirectResponse
    {
        $this->authorize('view', $application);

        $request->validate([
            'document' => ['required', 'file'],
        ]);

        $file     = $request->file('document');
        $filepath = $file->store("documents/{$application->id}", 'local');

        $document = $application->documents()->create([
            'uploaded_by' => Auth::id(),
            'filename' => $file->getClientOriginalName(),
            'filepath' => $filepath,
            'mimetype' => $file->getMimeType(),
        ]);

        $application->answers()->create([
            'template_field_id' => $templateField->id,
            'value'             => $file->getClientOriginalName(),
            'document_id'       => $document->id,
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    /**
     * Stream the document file to the browser to view or force download.
     * Requires review_applications in the document's organization. 
     */
    public function show(Request $request, Document $document): BinaryFileResponse
    {
        $this->authorize('view', $document);

        if (! Storage::disk('local')->exists($document->filepath)) {
            abort(404, 'The requested file could not be found.');
        }

        $path = Storage::disk('local')->path($document->filepath);

        // If the route was triggered with ?download=1
        if ($request->boolean('download')) {
            return response()->download($path, $document->filename);
        }

        return response()->file($path);
    }

    /**
     * Delete a document record and remove the file from storage. Requires
     * review_applications in the document's organization.
     */
    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        Storage::disk('local')->delete($document->filepath);
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }
}