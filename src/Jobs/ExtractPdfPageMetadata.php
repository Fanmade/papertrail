<?php

namespace Fanmade\Papertrail\Jobs;

use Fanmade\Papertrail\Contracts\PdfPageMetadataExtractor;
use Fanmade\Papertrail\Models\PdfPage;
use Fanmade\Papertrail\Traits\HasDocumentReference;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ExtractPdfPageMetadata implements ShouldQueue
{
    use Batchable, HasDocumentReference, Queueable;

    public function __construct(
        public string  $pdfPath,
        public ?string $documentId = null,
        public string  $disk = 'papertrail',
    ) {}

    public function handle(PdfPageMetadataExtractor $extractor): void
    {
        $absolute = Storage::disk($this->disk)->path($this->pdfPath);

        $doc = $this->getDocument($this->documentId, $this->pdfPath);

        if (!$doc) {
            return;
        }
        if ($doc->status->isError()) {
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
                    'page_number' => (int)$page['page_number'],
                    'width_pt' => (int)$page['width_pt'],
                    'height_pt' => (int)$page['height_pt'],
                ]
            );
            $count++;
        }

        $doc->update(['pages' => $count]);
    }
}
