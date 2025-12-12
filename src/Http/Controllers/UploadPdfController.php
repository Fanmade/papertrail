<?php

namespace Vqs\Papertrail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Vqs\Papertrail\Jobs\ExtractFormFields;
use Vqs\Papertrail\Jobs\ExtractPdfPageMetadata;
use Vqs\Papertrail\Jobs\GeneratePdfPageImages;
use Vqs\Papertrail\Jobs\GeneratePdfThumbnail;
use Vqs\Papertrail\Models\PdfDocument;

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

        // Ensure images are generated only after metadata extraction finished
        Bus::chain(
            [
                new ExtractPdfPageMetadata($file, $doc->id),
                new GeneratePdfPageImages($file, $doc->id),
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
