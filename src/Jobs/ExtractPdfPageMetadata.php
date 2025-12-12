<?php

namespace Vqs\Papertrail\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Vqs\Papertrail\Contracts\PdfPageMetadataExtractor;
use Vqs\Papertrail\Models\PdfPage;
use Vqs\Papertrail\Traits\HasDocumentReference;

class ExtractPdfPageMetadata implements ShouldQueue
{
    use Dispatchable, HasDocumentReference, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $pdfPath,
        public ?string $documentId = null,
        public string $disk = 'papertrail',
    ) {}

    public function handle(PdfPageMetadataExtractor $extractor): void
    {
        $absolute = Storage::disk($this->disk)->path($this->pdfPath);

        $doc = $this->getDocument($this->documentId, $this->pdfPath);

        if (! $doc) {
            return;
        }

        // Remove existing page rows to recalculate cleanly (metadata stage only)
        $doc->pages()->delete();

        $pages = $extractor->extract($absolute);

        $count = 0;
        foreach ($pages as $page) {
            /** @var array{page_number:int,width_pt:int,height_pt:int} $page */
            PdfPage::query()->create(
                [
                    'document_id' => $doc->id,
                    'page_number' => (int) $page['page_number'],
                    'width_pt' => (int) $page['width_pt'],
                    'height_pt' => (int) $page['height_pt'],
                ]
            );
            $count++;
        }

        $doc->update(['pages' => $count]);
    }
}
