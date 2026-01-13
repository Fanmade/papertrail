<?php

namespace Fanmade\Papertrail\Contracts;

use Carbon\CarbonInterface;

interface PdfPathBuilder
{
    public function disk(): string;

    /**
     * Build the processed root directory for a given uploaded PDF path (e.g., uploads/abc123.pdf)
     */
    public function rootDir(string $uploadedPdfPath, ?CarbonInterface $now = null): string;

    /**
     * Ensure the given directory exists and create it if it doesn't'.
     */
    public function ensureDir(string $dir): void;

    /**
     * Build the pages directory for a given uploaded PDF path (e.g., uploads/abc123.pdf/pages)
     * @param string $uploadedPdfPath
     * @param \Carbon\CarbonInterface|null $now
     * @return string
     */
    public function pagesDir(string $uploadedPdfPath, ?CarbonInterface $now = null): string;

    /**
     * Build the document PDF path for a given uploaded PDF path (e.g., uploads/abc123.pdf/document.pdf)
     * @param string $uploadedPdfPath
     * @param \Carbon\CarbonInterface|null $now
     * @return string
     */
    public function documentPdfPath(string $uploadedPdfPath, ?CarbonInterface $now = null): string;
}