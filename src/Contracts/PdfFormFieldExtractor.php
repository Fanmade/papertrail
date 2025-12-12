<?php

namespace Vqs\Papertrail\Contracts;

interface PdfFormFieldExtractor
{
    /**
     * @return array<\Vqs\Papertrail\Types\PdfFormField>
     */
    public function extractFields(string $pdfAbsolutePath): array;
}
