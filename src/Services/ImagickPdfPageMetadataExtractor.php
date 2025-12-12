<?php

namespace Vqs\Papertrail\Services;

use Imagick;
use Vqs\Papertrail\Contracts\PdfPageMetadataExtractor;

use function max;

class ImagickPdfPageMetadataExtractor implements PdfPageMetadataExtractor
{
    /**
     * {@inheritDoc}
     */
    public function extract(string $pdfAbsolutePath): array
    {
        // Use 72 DPI so that pixel values equal PostScript points (1 pt = 1 px at 72 DPI)
        $imagick = new Imagick;
        $imagick->setResolution(72, 72);
        $imagick->readImage($pdfAbsolutePath);

        $imagick = $imagick->coalesceImages();
        $results = [];
        $index = 0;
        foreach ($imagick as $page) {
            $index++;
            $geometry = $page->getImageGeometry();
            $widthPt = max(1, (int) $geometry['width']);
            $heightPt = max(1, (int) $geometry['height']);

            $results[] = [
                'page_number' => $index,
                'width_pt' => $widthPt,
                'height_pt' => $heightPt,
            ];
        }

        return $results;
    }
}
