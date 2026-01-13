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
        return response(status: Response::HTTP_FORBIDDEN)->noContent();
    }
}