<?php

namespace Fanmade\Papertrail\Http\Controllers;

use Fanmade\Papertrail\Models\PdfDocument;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeletePdfController
{
    public function __invoke(Request $request, PdfDocument $document)
    {
        /**
         * TODO: Implement deletion of documents
         * 1. Check if the user is allowed to delete the document. <- Make this configurable, if possible
         */
        if (! $this->deletionIsAllowed($document)) {
            return response(__('You are not allowed to delete this document'), status: Response::HTTP_FORBIDDEN)->isForbidden();
        }

        return response(__('Document deleted successfully'));
    }

    private function deletionIsAllowed(PdfDocument $document): bool
    {
        // TODO: Implement this!
        return false;
    }
}