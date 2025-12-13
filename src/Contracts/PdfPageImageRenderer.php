<?php

namespace Fanmade\Papertrail\Contracts;

/**
 * Generates page images and reports render metrics (no page-size extraction in points).
 */
interface PdfPageImageRenderer
{
    /**
     * Render all pages and return an array of per-page render info.
     *
     * Each item:
     *  - page_number: int (1-based)
     *  - width_px: int
     *  - height_px: int
     *  - dpi: int
     *  - image_path: string (disk-relative)
     *
     * @param  string  $pdfAbsolutePath  Absolute path on filesystem
     * @param  string  $baseFilename  base filename used for generated images
     * @param  array<string, mixed>  $options  override defaults (dpi, format, quality, background)
     * @return array<int, array<string, int|string>>
     */
    public function renderAllPages(string $pdfAbsolutePath, string $baseFilename, array $options = []): array;
}
