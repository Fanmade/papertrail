<?php

namespace Fanmade\Papertrail\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Imagick;
use ImagickPixel;
use Fanmade\Papertrail\Contracts\PdfImageGenerator;
use Fanmade\Papertrail\Contracts\PdfPageImageRenderer;

use function logger;
use function max;
use function pathinfo;
use function sprintf;
use function strtolower;
use function trim;

class ImagickPdfImageRenderer implements PdfImageGenerator, PdfPageImageRenderer
{
    /**
     * Generate a thumbnail for the first page of the PDF.
     *
     * {@inheritDoc}
     */
    public function generateThumbnail(string $pdfAbsolutePath, string $targetFilename, array $options = []): string
    {
        $config = config('papertrail.thumb_defaults');
        $thumbDisk = (string) Arr::get($options, 'disk', config('papertrail.thumb_disk'));
        $disk = Storage::disk($thumbDisk);
        // Allow overriding directory so thumbs can live inside processed dir
        $dir = trim((string) Arr::get($options, 'target_dir', config('papertrail.thumb_path')), '/');

        $format = strtolower(Arr::get($options, 'format', $config['format']));
        $width = (int) Arr::get($options, 'width', $config['width']);
        $height = (int) Arr::get($options, 'height', $config['height']);
        $density = (int) Arr::get($options, 'density', $config['density']);
        $quality = (int) Arr::get($options, 'quality', $config['quality']);
        $background = (string) Arr::get($options, 'background', $config['background']);

        $imagick = new Imagick;
        $imagick->setResolution($density, $density);
        $imagick->setBackgroundColor(new ImagickPixel($background));

        // First page only
        $imagick->readImage($pdfAbsolutePath . '[0]');
        $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
        $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

        // Resize with aspect ratio, best fit inside WxH
        $imagick->thumbnailImage($width, $height, true, true);

        if ($format === 'jpg' || $format === 'jpeg') {
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
            $imagick->setImageCompressionQuality($quality);
        } else {
            $imagick->setImageFormat('png');
        }

        $path = trim($dir . '/' . pathinfo($targetFilename, PATHINFO_FILENAME) . '.' . $format, '/');
        $disk->put($path, $imagick->getImageBlob(), $format === 'png' ? [] : 'public');
        $imagick->clear();

        return $path;
    }

    /**
     * Render all pages into images and return per-page render info.
     *
     * {@inheritDoc}
     */
    public function renderAllPages(string $pdfAbsolutePath, string $baseFilename, array $options = []): array
    {

        try {
            $cfg = config('papertrail.page_images');
            $diskName = (string) Arr::get($options, 'disk', Arr::get($cfg, 'disk'));
            $disk = Storage::disk($diskName);
            // Allow overriding directory so pages can live inside processed/<...>/pages
            $dir = trim((string) Arr::get($options, 'target_dir', Arr::get($cfg, 'path')), '/');
            $dpi = (int) Arr::get($options, 'dpi', Arr::get($cfg, 'dpi', 200));
            $format = strtolower((string) Arr::get($options, 'format', Arr::get($cfg, 'format', 'png')));
            $quality = (int) Arr::get($options, 'quality', Arr::get($cfg, 'quality', 90));
            $background = (string) Arr::get($options, 'background', Arr::get($cfg, 'background', '#FFFFFF'));
            $maxPages = max(1, (int) Arr::get($cfg, 'max_pages', 200));
            $imagick = new Imagick;
            $imagick->setResolution($dpi, $dpi);
            $imagick->setBackgroundColor(new ImagickPixel($background));
            $imagick->readImage($pdfAbsolutePath);
            $pageCount = $imagick->getNumberImages();
            $limit = max(0, min($pageCount, $maxPages));
            $results = [];
            for ($pageIndex = 0; $pageIndex < $limit; $pageIndex++) {
                // Select the specific page
                $imagick->setIteratorIndex($pageIndex);
                $page = $imagick->getImage(); // returns a copy of the current image

                // Flatten transparency and set format
                $page->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
                $page->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

                if ($format === 'jpg' || $format === 'jpeg') {
                    $page->setImageFormat('jpeg');
                    $page->setImageCompression(Imagick::COMPRESSION_JPEG);
                    $page->setImageCompressionQuality($quality);
                } else {
                    $page->setImageFormat('png');
                }

                $geometry = $page->getImageGeometry();
                $widthPx = (int) $geometry['width'];
                $heightPx = (int) $geometry['height'];

                // New naming: page-001.ext within provided directory
                $pageName = sprintf('page-%03d.%s', $pageIndex + 1, $format);
                $path = $dir . '/' . $pageName;

                $disk->put($path, $page->getImageBlob(), $format === 'png' ? [] : 'public');

                $results[] = [
                    'page_number' => $pageIndex + 1,
                    'width_px' => $widthPx,
                    'height_px' => $heightPx,
                    'dpi' => $dpi,
                    'image_path' => $path,
                ];

                // Free the cloned page resources
                $page->clear();
            }// end for
            // Free the main Imagick instance
            $imagick->clear();

            return $results;
        } catch (\ImagickException $e) {
            logger()->error('Failed to render pages 1', ['exception' => $e]);
        } catch (\ImagickPixelException $e) {
            logger()->error('Failed to render pages 2', ['exception' => $e]);
        } catch (\Throwable $e) {
            logger()->error('Failed to render pages 3', ['exception' => $e]);
        }

        return [];
    }
}
