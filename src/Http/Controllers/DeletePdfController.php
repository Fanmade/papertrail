<?php

namespace Fanmade\Papertrail\Http\Controllers;

use Fanmade\Papertrail\Contracts\PdfPathBuilder;
use Fanmade\Papertrail\Models\PdfDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DeletePdfController
{
    public function __construct(private PdfPathBuilder $pathBuilder) {}

    public function __invoke(Request $request, PdfDocument $document)
    {
        /**
         * TODO: Implement deletion of documents
         * 1. Check if the user is allowed to delete the document. <- Make this configurable, if possible
         */
        if (! $this->deletionIsAllowed($document)) {
            return response(__('You are not allowed to delete this document'), status: Response::HTTP_FORBIDDEN);
        }
        /**
         * 1. Delete all physical files
         * - Thumbnail
         * - Page images
         * - Original PDF
         * 2. Delete the data from the database
         */
        $this->deleteFiles($document);
        $document->delete();

        return response(__('Document deleted successfully'));
    }

    private function deletionIsAllowed(PdfDocument $document): bool
    {
        // TODO: Implement this!
        return true;
    }

    private function deleteFiles(PdfDocument $document): void
    {
        /**
         * The document is first uploaded to the uploads' directory.
         * It is then processed, thumbnail and page images are created, and then it is moved to the processed directory.
         */
        if ($document->status->isError()) {
            // We only need to delete the original PDF if the processing failed.
            Storage::delete($document->path);
            return;
        }

        // Delete the whole processed directory
        // TODO: Implement this!
    }
}