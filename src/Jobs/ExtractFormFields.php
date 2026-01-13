<?php

namespace Fanmade\Papertrail\Jobs;

use Fanmade\Papertrail\Contracts\PdfFormFieldExtractor;
use Fanmade\Papertrail\Models\PdfField;
use Fanmade\Papertrail\Traits\HasDocumentReference;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ExtractFormFields implements ShouldQueue
{
    use Batchable, HasDocumentReference, Queueable;

    public function __construct(
        public string  $pdfPath,
        public ?string $documentId = null,
        public string  $disk = 'papertrail',
    ) {}

    public function handle(PdfFormFieldExtractor $extractor): void
    {
        $absolute = Storage::disk($this->disk)->path($this->pdfPath);

        $fields = $extractor->extractFields($absolute);
        $doc = $this->getDocument($this->documentId, $this->pdfPath);
        if (!$doc) {
            return;
        }
        foreach ($fields as $field) {
            $fieldModel = PdfField::fromDto($field);
            $doc->fields()->save($fieldModel);
        }
    }
}
