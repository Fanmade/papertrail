<?php

namespace Vqs\Papertrail\Contracts;

/**
 * Extracts per-page metadata from a PDF without generating any images.
 */
interface PdfPageMetadataExtractor
{
    /**
     * Read the PDF and return metadata for each page.
     *
     * Each item:
     *  - page_number: int (1-based)
     *  - width_pt: int
     *  - height_pt: int
     *
     * @param  string  $pdfAbsolutePath  Absolute path on filesystem
     * @return array<int, array<string, int>>
     */
    public function extract(string $pdfAbsolutePath): array;
}
