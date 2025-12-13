<?php

namespace Fanmade\Papertrail\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Fanmade\Papertrail\Contracts\PdfPageImageRenderer;
use Fanmade\Papertrail\Services\ProcessedPathBuilder;
use Fanmade\Papertrail\Traits\HasDocumentReference;

class GeneratePdfPageImages implements ShouldQueue
{
    use Dispatchable, HasDocumentReference, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $pdfPath,
        public ?string $documentId = null,
        public string $disk = 'papertrail',
        public array $options = [],
    ) {}

    public function handle(PdfPageImageRenderer $renderer, ProcessedPathBuilder $paths): void
    {
        $absolute = Storage::disk($this->disk)->path($this->pdfPath);
        $base = pathinfo($this->pdfPath, PATHINFO_FILENAME);
        $doc = $this->getDocument($this->documentId, $this->pdfPath);

        if (! $doc) {
            return;
        }

        // Build processed directories: root and pages
        $rootDir = $paths->rootDir($this->pdfPath);
        $pagesDir = $paths->pagesDir($this->pdfPath);
        $disk = Storage::disk($paths->disk());
        $paths->ensureDir($rootDir);
        $paths->ensureDir($pagesDir);

        $options = $this->options;
        $options['target_dir'] = $pagesDir;
        $options['disk'] = $paths->disk();

        $result = $renderer->renderAllPages($absolute, $base, $options);

        foreach ($result as $data) {
            // {"page_number":1,"width_px":1654,"height_px":2339,"dpi":200,"image_path":"pages/pdfs/XWF6Uw6Q32BO1DfhF83KAvQK6sfixICSJCxqfqFJ-page-001.png"},{"page_number":2,"width_px":1654,"height_px":2339,"dpi":200,"image_path":"pages/pdfs/XWF6Uw6Q32BO1DfhF83KAvQK6sfixICSJCxqfqFJ-page-002.png"}
            // Update the page data for each page of the document
            $doc->pages()->updateOrCreate(
                [
                    'document_id' => $doc->id,
                    'page_number' => (int) $data['page_number'],
                ],
                [
                    'document_id' => $doc->id,
                    'page_number' => (int) $data['page_number'],
                    'width_px' => $data['width_px'],
                    'height_px' => $data['height_px'],
                    'dpi' => $data['dpi'],
                    'image_path' => $data['image_path'],
                ]
            );
        }
    }
}
