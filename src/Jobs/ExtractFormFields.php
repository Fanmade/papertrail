<?php

namespace Vqs\Papertrail\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Vqs\Papertrail\Contracts\PdfFormFieldExtractor;
use Vqs\Papertrail\Models\PdfField;
use Vqs\Papertrail\Traits\HasDocumentReference;

class ExtractFormFields implements ShouldQueue
{
    use Dispatchable, HasDocumentReference, InteractsWithQueue, Queueable;

    public function __construct(
        public string $pdfPath,
        public ?string $documentId = null,
        public string $disk = 'papertrail',
    ) {}

    public function handle(PdfFormFieldExtractor $extractor): void
    {
        $absolute = Storage::disk($this->disk)->path($this->pdfPath);

        $fields = $extractor->extractFields($absolute);
        $doc = $this->getDocument($this->documentId, $this->pdfPath);
        if (! $doc) {
            return;
        }
        foreach ($fields as $field) {
            $fieldModel = PdfField::fromDto($field);
            $doc->fields()->save($fieldModel);
        }
    }
}
