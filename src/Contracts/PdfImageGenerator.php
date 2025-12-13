<?php

namespace Fanmade\Papertrail\Contracts;

interface PdfImageGenerator
{
    /**
     * @return string Full storage path (disk-relative) to the generated thumbnail.
     */
    public function generateThumbnail(string $pdfAbsolutePath, string $targetFilename, array $options = []): string;
}
