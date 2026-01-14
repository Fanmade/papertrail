<?php

namespace Fanmade\Papertrail\Http\Controllers;

use Fanmade\Papertrail\Contracts\PdfPathBuilder;
use Fanmade\Papertrail\Models\PdfDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use function config;
use function response;

class DeletePdfController
{
    public function __invoke(Request $request, PdfDocument $document)
    {
        if (!$this->deletionIsAllowed($document)) {
            return response(__('You are not allowed to delete this document'), status: Response::HTTP_FORBIDDEN);
        }

        try {
            $this->deleteFiles($document);
            $document->delete();
        } catch (\Exception $e) {
            return response(
                [
                    'message' => __('Failed to delete document'),
                    'error' => $e->getMessage()
                ],
                status: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        return response(['message' => __('Document deleted successfully')]);
    }

    private function deletionIsAllowed(PdfDocument $document): bool
    {
        // TODO: Allow the user to configure which users can delete documents.
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

        $this->deleteProcessed($document);

        // We only need to delete the original PDF if the processing did not succeed
        if ($document->status->succeeded()) {
            return;
        }
        Storage::delete($document->path);
    }

    /**
     * @param \Fanmade\Papertrail\Models\PdfDocument $document
     * @return void
     */
    public function deleteProcessed(PdfDocument $document): void
    {
        $diskName = (string)config('papertrail.processed.disk', 'papertrail');
        $disk = Storage::disk($diskName);
        // Check if the directory exists
        if (!$disk->exists($document->path)) {
            return;
        }

        $disk->deleteDirectory($document->path);
    }
}