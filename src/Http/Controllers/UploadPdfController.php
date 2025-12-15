<?php

namespace Fanmade\Papertrail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Fanmade\Papertrail\Jobs\ExtractFormFields;
use Fanmade\Papertrail\Jobs\ExtractPdfPageMetadata;
use Fanmade\Papertrail\Jobs\GeneratePdfPageImages;
use Fanmade\Papertrail\Jobs\GeneratePdfThumbnail;
use Fanmade\Papertrail\Jobs\FinalizeProcessedPdf;
use Fanmade\Papertrail\Models\PdfDocument;

use function response;

class UploadPdfController
{
    public function __invoke(Request $request)
    {
        $uploadedFile = $request->file('pdf');
        if (! $uploadedFile) {
            return response()->json(['success' => false]);
        }
        $file = $uploadedFile->store('uploads', 'papertrail');

        if (! $file) {
            return response()->json(['success' => false]);
        }
        $doc = PdfDocument::query()->create(
            [
                'name' => $uploadedFile->getClientOriginalName(),
                'path' => $file,
                'mime' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
                'pages' => 0,
            ]
        );

        // Generate thumbnail independently
        GeneratePdfThumbnail::dispatch($file);

        // Ensure images are generated only after metadata extraction finished and finalize at the end
        Bus::chain(
            [
                new ExtractPdfPageMetadata($file, $doc->id),
                new GeneratePdfPageImages($file, $doc->id),
                new FinalizeProcessedPdf($file, $doc->id),
            ]
        )->dispatch();

        // Other jobs remain independent
        ExtractFormFields::dispatch($file, documentId: $doc->id);

        return response()->json(
            [
                'success' => true,
                'message' => __('File uploaded successfully'),
            ]
        );
    }
}
