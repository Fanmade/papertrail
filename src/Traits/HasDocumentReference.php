<?php

namespace Fanmade\Papertrail\Traits;

use Fanmade\Papertrail\Models\PdfDocument;

trait HasDocumentReference
{
    public function getDocument(?string $documentId = null, ?string $path = null): ?PdfDocument
    {
        if ($documentId) {
            $doc = PdfDocument::find($documentId);
            if ($doc) {
                return $doc;
            }
        }

        if ($path) {
            return PdfDocument::where('path', $path)->first();
        }

        return null;
    }
}
