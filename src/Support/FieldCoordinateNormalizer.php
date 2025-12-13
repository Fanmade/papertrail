<?php

namespace Fanmade\Papertrail\Support;

final class FieldCoordinateNormalizer
{
    /**
     * Convert absolute PDF coordinates to percentage-based CSS coordinates.
     *
     * @return array{left: float, top: float, width: float, height: float}
     */
    public static function toPercentages(
        float $x,
        float $y,
        float $width,
        float $height,
        int $pageWidth,
        int $pageHeight,
        string $origin = 'top-left'
    ): array {
        $toPercent = static function (float $value, int $total): float {
            if ($total <= 0) {
                return 0.0;
            }
            $pct = ($value / $total) * 100.0;
            return max(0.0, min(100.0, $pct));
        };

        $leftPct = $toPercent($x, $pageWidth);
        $widthPct = $toPercent($width, $pageWidth);
        $heightPct = $toPercent($height, $pageHeight);

        $origin = strtolower($origin);
        if ($origin === 'bottom-left') {
            // Convert PDF-like Y (origin at bottom-left) to CSS top-left
            $topPct = 100.0 - $toPercent($y + $height, $pageHeight);
        } else { // default top-left
            $topPct = $toPercent($y, $pageHeight);
        }

        return [
            'left' => $leftPct,
            'top' => $topPct,
            'width' => $widthPct,
            'height' => $heightPct,
        ];
    }
}
