<?php

namespace Fanmade\Papertrail\Contracts;

interface PdfFormFieldExtractor
{
    /**
     * @return array<\Fanmade\Papertrail\Types\PdfFormField>
     */
    public function extractFields(string $pdfAbsolutePath): array;
}
