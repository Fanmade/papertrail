<?php

namespace Fanmade\Papertrail\Contracts;

use Fanmade\Papertrail\Models\PdfDocument;

interface FormFiller
{
    /**
     * @return string Full storage path to the filled PDF.
     */
    public function execute(PdfDocument $document, array $contextData, string $savePath = null): string;
}