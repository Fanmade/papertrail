<?php

namespace Fanmade\Papertrail\Jobs;

use Fanmade\Papertrail\Contracts\PdfPageImageRenderer;
use Fanmade\Papertrail\Contracts\PdfPathBuilder;
use Fanmade\Papertrail\Traits\HasDocumentReference;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GeneratePdfPageImages implements ShouldQueue
{
    use HasDocumentReference, Queueable;

    public function __construct(
        public string  $pdfPath,
        public ?string $documentId = null,
        public string  $disk = 'papertrail',
        public array   $options = [],
    ) {}

    public function handle(PdfPageImageRenderer $renderer, PdfPathBuilder $paths): void
    {
        $absolute = Storage::disk($this->disk)->path($this->pdfPath);
        $base = pathinfo($this->pdfPath, PATHINFO_FILENAME);
        $doc = $this->getDocument($this->documentId, $this->pdfPath);

        if (!$doc) {
            return;
        }

        // Build processed directories: root and pages
        $rootDir = $paths->rootDir($this->pdfPath);
        $pagesDir = $paths->pagesDir($this->pdfPath);
//        $disk = Storage::disk($paths->disk()); // Check: Can this be removed?
        $paths->ensureDir($rootDir);
        $paths->ensureDir($pagesDir);

        $options = $this->options;
        $options['target_dir'] = $pagesDir;
        $options['disk'] = $paths->disk();

        $result = $renderer->renderAllPages($absolute, $base, $options);

        foreach ($result as $data) {
            // Update the page data for each page of the document
            $doc->pages()->updateOrCreate(
                [
                    'document_id' => $doc->id,
                    'page_number' => (int)$data['page_number'],
                ],
                [
                    'document_id' => $doc->id,
                    'page_number' => (int)$data['page_number'],
                    'width_px' => $data['width_px'],
                    'height_px' => $data['height_px'],
                    'dpi' => $data['dpi'],
                    'image_path' => $data['image_path'],
                ]
            );
        }
    }
}
